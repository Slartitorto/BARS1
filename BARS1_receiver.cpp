// README
// BARS1_receiver.cpp
// nRF24L01+ su RaspberryPi
// code for data RX
// connect:
// nrf24L01:     1   2   3   4   5   6   7
// RaspberryPi:  6   1   22  24  23  19  21

// then compile by:
// # g++ -Ofast -mfpu=vfp -mfloat-abi=hard -march=armv6zk -mtune=arm1176jzf-s -Wall -I../ -lrf24-bcm -lconfig BARS1_receiver.cpp -o BARS1_receiver
// Make sure configuratione file exists /usr/local/etc/BARS1_receiver.conf:

// # BARS_listen configration file (/usr/local/etc/BARS1_receiver.conf)
//
// # KEY string for encryption (32 x [0-9]char)
// KEY = "42892649286749187649187263498176"
//
// # datasink server url
// URL = "http://BARS.sample.com"
//
// # Router name
// ROUTER = "000001"

// check if wget perform correctly with server bars.slartitoro.eu:
// # wget -q -O /dev/null URL/datasink.php?data=12334567890
// copy the binary in proper location:
// # cp BARS1_receiver /usr/local/bin
// for start at boot, edit a proper init.d file using some other script as base:
// # vi /etc/init.d/BARS1_receiver
// # chmod 755 /etc/init.d/BARS1_receiver
// # sudo update-rc.d BARS1_receiver defaults
// then start the binary:
// # /etc/init.d/BARS1_receiver start

#include <cstdlib>
#include <iostream>
#include <RF24/RF24.h>
#include <stdio.h>
#include <string.h>
#include <stdlib.h>
#include <time.h>
#include <libconfig.h>

RF24 radio(RPI_V2_GPIO_P1_22, RPI_V2_GPIO_P1_24, BCM2835_SPI_SPEED_8MHZ);

int main(int argc, char** argv)
{
  // Read config file

  config_t cfg, *cf;
  const char *key = NULL;
  const char *url = NULL;
  const char *router = NULL;

  cf = &cfg;
  config_init(cf);

  if (!config_read_file(cf, "/usr/local/etc/BARS1_receiver.conf")) {
    fprintf(stderr, "%s:%d - %s\n",
    config_error_file(cf),
    config_error_line(cf),
    config_error_text(cf));
    config_destroy(cf);
    return(EXIT_FAILURE);
  }
  if (config_lookup_string(cf, "KEY", &key))
  printf("Key: %s\n", key);

  if (config_lookup_string(cf, "URL", &url))
  printf("Url: %s\n", url);

  if (config_lookup_string(cf, "ROUTER", &router))
  printf("Router: %s\n", router);

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


  while(1) {

    // *** 32 byte character array is max payload
    char receivePayload[32]="";

    // *** Sleep 20 ms
    usleep(20000);

    while (radio.available())
    {
      // *** Read from radio until payload size is reached
      uint8_t len = radio.getDynamicPayloadSize();
      radio.read(receivePayload, len);

      // *** Separator ":" count
      const char * z = receivePayload;
      int separator_count = 0;
      int m;
      for (m=0; z[m]; m++)
      {
        if(z[m] == ':') {separator_count ++;}
      }

      // *** OK if 5 or 6 separators (BARS0 or BARS1 hardware)
      if (separator_count == 5 || separator_count == 6)
      {
        char receivePayload_crypted[32]="";
        unsigned int i;
        for(i=0;i<strlen(receivePayload);++i)
        {
          receivePayload_crypted[i] = receivePayload[i] + key[i] -30;
        }

        char wget_command[256];
        sprintf(wget_command,"wget -q -b -O /dev/null \"%s/datasink.php?data=%s&router=%s\"",url,receivePayload_crypted,router);
        system(wget_command);
      }
    }
  }
}
