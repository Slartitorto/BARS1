<?php

include "db_connection.php";

//	FUNZIONI
function SettaCoockie($ID_UTENTE, $TEMPO)
{
  setcookie('LOGIN', $ID_UTENTE, time()+$TEMPO);	//IDENTIFICATIVO DELL'UTENTE
}
function PasswordRandom()
{
  $PASSWORD = "";
  mt_srand();

  for($i=0; $i<6; $i++)
  {
    $PASSWORD .= "".mt_rand(0, 9)."";
  }
  return $PASSWORD;
}


if(@$_GET["act"]	==	"recuperaPassword")
{
  $Sql		=	"SELECT * FROM `utenti` WHERE `email`='".@$_POST["email"]."' AND `stato`='1';";
  $result		=	$conn->query($Sql);
  if(($result->num_rows) == 1)
  {
    $Dati		=	$result->fetch_array();
    $Password	=	PasswordRandom();
    $Sql		=	"UPDATE `utenti` SET `password` = '".md5($Password)."' WHERE `codUtente`='".$Dati["codUtente"]."' LIMIT 1 ;";
    $Query		=	$conn->query($Sql);

    $to = $_POST["email"];
    $subject = "BAsic Remote Sensors - Recupero Password";
    $message	=	"Ciao ".$Dati["username"].",\n
    questa e-mail ti giunge perchè hai richiesto il cambio password.\n\n
    Di seguito trovi il dati di accesso all'area riservata del servizio BARS.\n\n
    Username: ".$Dati["username"]."\n
    Password: ".$Password."\n\n\n
    In caso di problemi ti invitiamo a contattarci direttamente all'indirizzo admin@slartitorto.eu
    ";
    $headers = "From: root@slartitorto.eu \r\n" .
    "Reply-To: root@slartitorto.eu \r\n";
    mail($to, $subject, $message, $headers);

    header('Location: index.php?act=RecuperoOn');
  }
  else
  {
    header('Location: index.php?act=RecuperoOff');
  }
}
else if(@$_GET["act"]	==	"login")
{
  $codPassword	=	md5($_POST["password"]);
  $Sql		=	"SELECT * FROM `utenti` WHERE `username`='".@$_POST["username"]."' AND `password`='".$codPassword."' AND `stato`='1';";
  $result		=	$conn->query($Sql);
  if(($result->num_rows) == 1)
  {
    $Dati		=	$result->fetch_array();
    if(@$_POST["ricorda"]	==	1)
    $TempoDiValidita	=	31536000;
    else
    $TempoDiValidita	=	72000;
    SettaCoockie($Dati["codUtente"], $TempoDiValidita);
    header('Location: status.php');
  }
  else
  {
    header('Location: index.php');
  }
}
else if(@$_GET["act"]	==	"logout")
{
  SettaCoockie(" ", -1);
  header('Location: index.php');
}
else if(@$_GET["act"]	==	"conferma")
{
  $Sql		=	"UPDATE `utenti` SET `stato` = '1' WHERE `codUtente`='".@$_GET["cod"]."' LIMIT 1 ;";
  $result		=	$conn->query($Sql);
  header('Location: index.php');
}

?>
