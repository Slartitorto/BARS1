// test.cpp
// nRF24L01+ su RaspberryPi simple receive
// print out what received
// example code for data RX on standard output
// connect:
// nrf24L01:     1   2   3   4   5   6   7
// RaspberryPi:  6   1   22  24  23  19  21

// compile with:
// g++ -Ofast -mfpu=vfp -mfloat-abi=hard -march=armv6zk -mtune=arm1176jzf-s -Wall -I../ -lrf24-bcm test.cpp -o test

// OBSOLETO: non funziona con sensori firmware 2.01

#include <cstdlib>
#include <iostream>
#include <RF24/RF24.h>

using namespace std;

RF24 radio(RPI_BPLUS_GPIO_J8_22, RPI_BPLUS_GPIO_J8_24, BCM2835_SPI_SPEED_8MHZ);


void setup(void)
{
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
    // 32 byte character array is max payload
    char receivePayload[32]="";

    while (radio.available())
    {
        // read from radio until payload size is reached
        uint8_t len = radio.getDynamicPayloadSize();
        radio.read(receivePayload, len);

        // display payload
        cout << receivePayload << endl;
    }
}

int main(int argc, char** argv)
{
    setup();
    while(1)
        loop();

    return 0;
}
