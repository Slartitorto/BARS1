
<?php
//keep_alive_datasink.php

include "db_connection.php";

if(isset($_GET['router'])){
  $router=$_GET['router'];
} else exit();

$result = mysqli_query($conn,"delete from keep_alive_check where router = '$router'");
$result = mysqli_query($conn,"insert into keep_alive_check (router) values ('$router')");

mysqli_close($conn);
?>
