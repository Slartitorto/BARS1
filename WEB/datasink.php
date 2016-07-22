<?php
if(isset($_GET['data']))
{
  $data=$_GET['data'];
  system("./manage_record $data");
}
?>
