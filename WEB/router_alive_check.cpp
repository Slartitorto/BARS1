// g++ `mysql_config --cflags` `mysql_config --libs` -o ppp ppp.cpp
#include <cstdlib>
#include <stdio.h>
#include <string.h>
#include <stdlib.h>
#include <unistd.h>
#include <mysql/mysql.h>

#define DATABASE_SERVER "localhost"
#define DATABASE_NAME "sensors"
#define DATABASE_USERNAME "DATABASE_USERNAME"
#define DATABASE_PASSWORD "DATABASE_PASSWORD"

int main(void)
{
  //initialize MYSQL object for connections
  MYSQL *mysql_conn =  mysql_init(NULL);
  // *** Connect to the database
  mysql_real_connect(mysql_conn, DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME, 0, NULL, 0);

  char query[1024];
  char subquery[512];
  char mail_command[512];
  char* router[16];
  char* timestamp[64];
  char* email[64];
  MYSQL_ROW row;

  while(1) {

    sprintf(query, "SELECT router, timestamp from keep_alive_check where alarmed = 0 and timestamp < now() - interval 150 second");
    // printf("query =%s\n",query);
    mysql_query(mysql_conn,query);
    MYSQL_RES *result = mysql_store_result(mysql_conn);
    int x = 0;
    while ((row = mysql_fetch_row(result))) {
      router[x] = row[0];
      timestamp[x] = row[1];
      // printf("timestamp: %s - router: %s\n",timestamp[x],router[x]);
      x++;
    }

    for (int i=0; i<x; i++) {
      // printf("timestamp: %s - router: %s\n",timestamp[i],router[i]);
      // metti a 1 il flag "alarmed" nella tabella keep_alive_check
      sprintf(query, "UPDATE keep_alive_check set alarmed = 1 where router = %s",router[i]);
      printf("update db: query = %s \n",query);
      mysql_query(mysql_conn,query);

      sprintf(subquery,"select tenant from devices,last_rec_data where devices.serial = last_rec_data.serial and last_rec_data.router = '%s'",router[i]);
      sprintf(query,"select distinct utenti.email from utenti where (utenti.t0 in (%s) or utenti.t1 in (%s) or utenti.t2 in (%s) or utenti.t3 in (%s))",subquery,subquery,subquery,subquery);

      // printf("query = %s\n",query);
      mysql_query(mysql_conn,query);
      MYSQL_RES *result = mysql_store_result(mysql_conn);
      int x = 0;
      while ((row = mysql_fetch_row(result))) {
        email[x] = row[0];
        // printf("email: %s \n",email[x]);

        // send email
        sprintf(mail_command,"echo \"Allarme da router %s; non ricevo dati da 2 min.\"|mail -r root@slartitorto.eu -s \"Allarme di collegamento router %s\" %s",router[i],router[i],email[x]);
        printf("send mail as: %s \n\r",mail_command);
        system(mail_command);

        x++;
      }
    }
    mysql_free_result(result);
    sleep(15);

  }
}
