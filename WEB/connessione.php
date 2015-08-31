<?php

// DATI DI CONNESSIONE AL DATABASE
	$mysql_host = "localhost";
	$mysql_user = "USER";
	$mysql_password = "PWD";
	$mysql_db = "sensors";

//	ACCESSO AL DATABASE
	$pink = mysql_connect($mysql_host, $mysql_user, $mysql_password) or die($messaggio_errore_connessione_db);
	mysql_select_db($mysql_db) or die($errore_selezione_db);

	define("NOMESITO", "Home Sensors");
	define("URLSITO", "http://bars.slartitorto.eu");
?>
