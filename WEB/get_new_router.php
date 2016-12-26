<?php
include "db_connection.php";

$query = "SELECT router_name,router_key from new_routers order by router_name limit 1";
$result = $conn->query($query);

while($row = $result->fetch_assoc()) {
  $router_name = $row["router_name"];
  $current_key = $row["router_key"];
}

echo $router_name . ":" . $current_key;

$query = "DELETE from new_routers where router_name = $router_name";
$result = $conn->query($query);

$query = "INSERT into router (router,current_key) values ('$router_name','$current_key')";
$result = $conn->query($query);

mysqli_close($conn);
?>
