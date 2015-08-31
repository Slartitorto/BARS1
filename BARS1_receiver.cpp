// README

// BARS1_receiver.cpp
// nRF24L01+ su RaspberryPi
// example code for data RX
// connect:
// nrf24L01:     1   2   3   4   5   6   7
// RaspberryPi:  6   1   22  24  23  19  21

// before compile, do:
// # sudo apt-get update
// # sudo apt-get install mysql-client
// # sudo apt-get install libmysqlclient-dev
// then compile by:
// # g++ -Ofast -mfpu=vfp -mfloat-abi=hard -march=armv6zk -mtune=arm1176jzf-s -Wall -I../ -lrf24-bcm BARS1_receiver.cpp -o BARS1_receiver `mysql_config --cflags` `mysql_config --libs`
// copy the binary in proper location:
// # cp BARS1_receiver /usr/local/bin
// for start at boot, edit a proper init.d file using some other script as base:
// # vi /etc/init.d/BARS1_receiver
// # chmod 755 /etc/init.d/BARS1_receiver
// # sudo update-rc.d BARS1_receiver defaults
// then start the binary:
// # /etc/init.d/BARS1_receiver start
// remember to insert the device in database; without it, segmentation fault


#include <cstdlib>
#include <iostream>
#include <RF24/RF24.h>
#include <stdio.h>
#include <string.h>
#include <stdlib.h>
#include <mysql/mysql.h>
#include <mysql/my_global.h>
#include <time.h>

#define DATABASE_HOST "XXX"
#define DATABASE_NAME  "XXX"
#define DATABASE_USERNAME "XXX"
#define DATABASE_PASSWORD "XXX"

MYSQL mysql_conn;
using namespace std;

RF24 radio(RPI_V2_GPIO_P1_22, RPI_V2_GPIO_P1_24, BCM2835_SPI_SPEED_8MHZ);

void setup(void)
{
// *** Initialize MYSQL object for connections
mysql_init(&mysql_conn);

// my_bool reconnect = 1;
// mysql_options(&mysql_conn,MYSQL_OPT_RECONNECT,&reconnect);

// *** Connect to the database
int m = 0;
while(mysql_real_connect(&mysql_conn, DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME, 0, NULL, 0) == NULL)
{
	fprintf(stderr, "%s\n\r", mysql_error(&mysql_conn));
	m++;
	if (m > 15) {exit(0);}
	sleep(15);
}
printf("Database connection successful.\r\n");

// char mail_command[256];
// sprintf(mail_command,"echo \"Allarme da sensore di temperatura test test; temperatura rilevata = 22.2f - out of range (min = 2.3 - max 54)\"|mail -r Sensors -s \"Allarme \" ivano.tortolini@gmail.com");
// system(mail_command);

// init radio for reading
radio.begin();
radio.enableDynamicPayloads();
radio.setAutoAck(1);
radio.setRetries(15,15);
radio.setDataRate(RF24_250KBPS);
radio.setPALevel(RF24_PA_MAX);
radio.setChannel(76);
radio.setCRCLength(RF24_CRC_16);
radio.openReadingPipe(1,0xF0F0F0F0E1LL);
radio.startListening();
}

void loop(void)
{
// *** 32 byte character array is max payload
char receivePayload[32]="";

// *** Sleep 20 ms
usleep(20000);

while (radio.available())
{
// *** Read from radio until payload size is reached
uint8_t len = radio.getDynamicPayloadSize();
radio.read(receivePayload, len);

// *** Logging received data
time_t current_time;
char* c_time_string;
current_time = time(NULL);
c_time_string = ctime(&current_time);
printf("%s", c_time_string);
cout << receivePayload << endl;

// *** Separator ":" count
const char * z = receivePayload;
int separator_count;
int m;
separator_count = 0;
for (m=0; z[m]; m++) {
	if(z[m] == ':') {
		separator_count ++;
		}
	}

// *** OK if 6 separators
if (separator_count == 6) {

int data_type = 0;
char * serial;
int counter = 0;
float data = 0;
float battery = 0;
int period = 0;

char mail_command[256];

data_type = atoi(strtok (receivePayload, ":"));
serial = strtok (NULL, ":");
counter = atoi(strtok (NULL, ":"));
data = atof(strtok (NULL, ":")) / 100;
battery = atof(strtok (NULL, ":")) / 1000;
period = atoi(strtok (NULL, ":"));

// *** Insert data into DB
char query[256];
printf("INSERT INTO rec_data (data_type,serial,counter,data,battery,period) VALUES (%04d,'%s',%04d,%.2f,%.3f,%04d)\n", data_type, serial, counter, data, battery,period);
sprintf(query, "INSERT INTO rec_data (data_type,serial,counter,data,battery,period) VALUES (%04d,'%s',%04d,%.2f,%.3f,%04d)", data_type, serial, counter, data, battery,period);
mysql_query(&mysql_conn,query);

// *** Get useful data from DB
sprintf(query, "select armed, alarmed, min_ok, max_ok, device_name, position, tenant from devices where serial = '%s'", serial);
mysql_query(&mysql_conn,query);

MYSQL_RES *result = mysql_store_result(&mysql_conn);
MYSQL_ROW row = mysql_fetch_row(result);

int armed = atoi(row[0]);
int alarmed = atoi(row[1]);
int min_ok = atoi(row[2]);
int max_ok = atoi(row[3]);
char* device_name = row[4];
char* position = row[5];
char* tenant = row[6];

// *** Get email address for alarm sending
sprintf(query, "select email,email2 from utenti where idUtente = '%s'", tenant);
mysql_query(&mysql_conn,query);

MYSQL_RES *mail_result = mysql_store_result(&mysql_conn);
MYSQL_ROW mail_row = mysql_fetch_row(mail_result);

char* email = mail_row[0];
char* email2 = mail_row[1];

// *** LOG
printf("min = %d - max = %d \n\r",min_ok,max_ok);
printf("data = %f \n\r",data);
printf("armed = %d - alarmed = %d\n\r",armed,alarmed);
printf("sensore = %s %s\n\r", device_name, position);
printf("email = %s email2 = %s\n\r", email, email2);

// *** If alarm
if ((data < min_ok) or (data > max_ok)) {
		printf("alarm \n\r");
		if ((armed == 1) and (alarmed == 0)){
			printf("send mail \n\r");
			sprintf(mail_command,"echo \"Allarme da sensore di temperatura %s %s; temperatura rilevata = %.2f - out of range (min = %d - max %d)\"|mail -r Sensors -s \"Allarme %s %s\" %s %s",device_name,position,data,min_ok,max_ok,device_name,position,email,email2);
			system(mail_command);
			sprintf(query, "update devices set alarmed = 1 where serial = '%s'", serial);
			mysql_query(&mysql_conn,query);
		}
	}
// *** If not alarm
else {
	// *** If previously alarmed, reset alarm flag
	if (alarmed == 1){
		sprintf(query, "update devices set alarmed = 0 where serial = '%s'", serial);
                mysql_query(&mysql_conn,query);
	}
}

// *** Free array
mysql_free_result(result);
mysql_free_result(mail_result);

	}
    }
}

int main(int argc, char** argv)
{
    setup();
    while(1)
        loop();
    return 0;
}
