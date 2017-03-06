<?php

// DATI DI CONNESSIONE AL DATABASE
$servername = "localhost";
$username = "USER";
$password = "PWD";
$dbname = "sensors";


//      ACCESSO AL DATABASE
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
        exit();
}

define("NOMESITO", "Hooly");
define("URLSITO", "http://bars.slartitorto.eu");
?>
