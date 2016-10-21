<?php
if(isset($_GET['data']))
   {
      $data=$_GET['data'];
      $router=$_GET['router'];
      system("./manage_record $data $router");
   }
?>
