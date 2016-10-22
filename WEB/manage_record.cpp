//
// compile with:
// g++ -Wall -I../ -o manage_record manage_record.cpp `mysql_config --cflags` `mysql_config --libs`
// questo manage_record.cpp è chiamato come system command da datasink.php:
// per questo va messo nella directory dove sta datasink.php (/var/www/bars/)

// <?php
// if(isset($_GET['data']))
// {
// $data=$_GET['data'];
// $router=$_GET['router'];
// system("./manage_record $data $router");
// }
// ?>

#include <stdio.h>
#include <string.h>
#include <mysql/mysql.h>
#include <mysql/my_global.h>

#define DATABASE_HOST "localhost"
#define DATABASE_NAME  "sensors"
#define DATABASE_USERNAME "username"
#define DATABASE_PASSWORD "password"

int main(int argc, char **argv)
{
  if (argc != 3) return(0);

  MYSQL mysql_conn;
  using namespace std;
  mysql_init(&mysql_conn);
  mysql_real_connect(&mysql_conn, DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME, 0, NULL, 0);

  char ReceivedPaiload[32];
  int m;
  for (m=0; m<32; m++) {ReceivedPaiload[m] = 0; }
  char ReceivedPaiload_crypted[32];
  char router[32];
  sprintf(ReceivedPaiload_crypted,argv[1]);
  sprintf(router,argv[2]);

  char query[256];
  sprintf(query, "select current_key from router where router = '%s'", router);
  mysql_query(&mysql_conn,query);
  MYSQL_RES *result = mysql_store_result(&mysql_conn);
  MYSQL_ROW row = mysql_fetch_row(result);
  char* key = row[0];

  unsigned int i;
  for(i=0;i<strlen(argv[1]);++i)
  {
    ReceivedPaiload[i] = ReceivedPaiload_crypted[i] - key[i] +30;
  }

  const char * z = ReceivedPaiload;
  int separator_count = 0;
  for (m=0; z[m]; m++) { if(z[m] == ':') { separator_count ++; } }

  if (separator_count == 5 || separator_count ==6) {
    int data_type = 0;
    char * serial;
    int counter = 0;
    float data = 0;
    float battery = 0;
    int period = 0;
    data_type = atoi(strtok (ReceivedPaiload, ":"));
    serial = strtok (NULL, ":");
    counter = atoi(strtok (NULL, ":"));
    data = atof(strtok (NULL, ":")) / 100;
    battery = atof(strtok (NULL, ":")) / 1000;
    if (separator_count == 6) { period = atoi(strtok (NULL, ":")); } else period = 300;

    sprintf(query, "DELETE from last_rec_data where serial = '%s'", serial);
    mysql_query(&mysql_conn,query);

    sprintf(query, "INSERT INTO last_rec_data (data_type,serial,counter,data,battery,period,router) VALUES (%04d,'%s',%04d,%.2f,%.3f,%04d,'%s')", data_type, serial, counter, data, battery,period,router);
    mysql_query(&mysql_conn,query);

    sprintf(query, "INSERT INTO rec_data (data_type,serial,counter,data,battery,period,router) VALUES (%04d,'%s',%04d,%.2f,%.3f,%04d,'%s')", data_type, serial, counter, data, battery,period,router);
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

    // *** If alarm
    if ((data < min_ok) or (data > max_ok)) {
      // printf("alarm \n\r");
      if ((armed == 1) and (alarmed == 0)){

        // *** Get email address for alarm sending
        sprintf(query, "select email from utenti where t0 = '%s' or t1 = '%s' or t2 = '%s' or t3 = '%s'", tenant, tenant, tenant, tenant);
        // printf("query = %s\n\r",query);
        mysql_query(&mysql_conn,query);
        result = mysql_store_result(&mysql_conn);

        char* email[8];
        int x = 0;
        while ((row = mysql_fetch_row(result)))
        {
          email[x] = row[0];
          x++;
        }

        for (int i=0;i<x;i++){
          // printf("email n. %d = %s\n\r", i, email[i]);
          // printf("send mail \n\r");
          char mail_command[256];
          sprintf(mail_command,"echo \"Allarme da sensore di temperatura %s %s; temperatura rilevata = %.2f - out of range (min = %d - max = %d)\"|mail -r root@slartitorto.eu -s \"Allarme %s %s\" %s",device_name,position,data,min_ok,max_ok,device_name,position,email[i]);
          system(mail_command);
        }
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
  }
}
