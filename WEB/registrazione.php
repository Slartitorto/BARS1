<?php
	include "db_connection.php";

	if(@$_POST["invio"])
	{
	$codUtente	=	md5($_POST["email"]);
	$codPassword	=	md5($_POST["password"]);
	$Sql		=	"INSERT INTO `utenti` SET `username`='".@$_POST["username"]."', `codUtente`='".$codUtente."', `password`='".$codPassword."', `email`='".@$_POST["email"]."';";
	$result		=	$conn->query($Sql);
	$Messaggio	=	"
	Buongiorno ".$_POST["username"].",\n
	questa e-mail ti giunge dall'area riservata del sito ".NOMESITO.".\n\n
	Di seguito trovi l'url per procedere all'attivazione del tuo account.\n\n
	Dati di accesso\n
	Username: ".$_POST["username"]."\n
	Password: ".$_POST["password"]."\n\n\n
	Url di conferma: ".URLSITO."/azioni.php?act=conferma&cod=".$codUtente."\n\n
	In caso di problemi ti invitiamo a contattarci direttamente.
	";

	mail($_POST["email"], "Home Sensors - Conferma registrazione", $Messaggio, "From: registrazione@slatitorto.eu");
	header('Location: index.php?act=RegistrazioneOn');
		}
?>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo NOMESITO; ?></title>
<link href="stile.css" rel="stylesheet" type="text/css" />
</head>

<body>
<SCRIPT language="javascript">
	function conferma()
		{
			c=1;
			if (document.formRegistrazione.username.value	=='') { c=0; }
			if (document.formRegistrazione.password.value	=='') { c=0; }
			if (document.formRegistrazione.password2.value	=='') { c=0; }
			if (document.formRegistrazione.email.value	=='') { c=0; }
			if (c==1)
				{
					if (document.formRegistrazione.password.value	==	document.formRegistrazione.password2.value)
						{
						}
					else
						{
							alert('Le password non corrispondono');
							document.formRegistrazione.submit()=false;
						}
					document.formRegistrazione.submit();
				}
			else
				{
					alert('Tutti i campi sono obbligatori');
				}
		}
</SCRIPT>

<h1><?php echo NOMESITO; ?></h1>

<p>
<form id="formRegistrazione" name="formRegistrazione" method="post" action="">
  <table width="500" align="center" >
    <tr>
      <td width="200" align="left" valign="left"><h2>Registra nuovo utente</h2></td><td></td>
    </tr>
    <tr>
      <td height="24" align="left" valign="middle">Username:</td>
      <td height="24" align="left" valign="middle"><input size="40" name="username" type="text" class="stileCampiInput" id="username" /></td>
    </tr>
    <tr>
      <td height="24" align="left" valign="middle">Password:</td>
      <td height="24" align="left" valign="middle"><input size="40" name="password" type="password" class="stileCampiInput" id="password" /></td>
    </tr>
    <tr>
      <td height="24" align="left" valign="middle">Ripeti Password:</td>
      <td height="24" align="left" valign="middle"><input size="40" name="password2" type="password" class="stileCampiInput" id="password2" /></td>
    </tr>
    <tr>
      <td height="24" align="left" valign="middle">E-mail:</td>
      <td height="24" align="left" valign="middle"><input size="40" name="email" type="text" class="stileCampiInput" id="email" /></td>
    </tr>
    <tr>
      <td height="24" colspan="2" align="center" valign="middle"><input name="invio" type="hidden" id="invio" value="1" />
      <input name="button" type="button" class="stileContattiButton" id="button" value="Registrati" onclick="conferma();" />
      </td>
    </tr>
    <tr>
      <td height="24" colspan="2" align="center" valign="middle"><a href="index.php" title="Torna alla pagina di log-in">Torna alla pagina di log-in</a><a href="recuperPassword.php" title="Ho smarrito la password"></a></td>
    </tr>
    <tr>
      <td height="24" colspan="2" align="center" valign="middle"><a href="index.php?act=Recovery" title="Ho smarrito la password">Ho smarrito la password</a><a href="recuperPassword.php" title="Ho smarrito la password"></a></td>
    </tr>
  </table>
</form>
</p>
</body>
</html>
