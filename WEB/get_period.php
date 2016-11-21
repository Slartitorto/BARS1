<?php
include "db_connection.php";

if(isset($_GET['serial']))
   {
      $serial=$_GET['serial'];

      $query = "SELECT code_period from devices WHERE serial = '$serial' ";
      $result = $conn->query($query);
      while($row = $result->fetch_assoc()) {
        $code_period = $row["code_period"];
      }
      echo $serial . ":" . $code_period ;
//      echo $code_period ;

   }
?>
