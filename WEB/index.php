<?php
// autenticazione con cookies da http://www.html.it/articoli/login-basati-su-cookie-con-php-1/
include "db_connection.php";
	if(isset($_COOKIE['LOGIN']))
		{
			$COD_UTENTE =	$_COOKIE['LOGIN'];
			header("Location: status.php");
		}
?>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo NOMESITO; ?></title>
<link href="stile.css" rel="stylesheet" type="text/css" />
</head>

<body>

<h1><?php echo NOMESITO; ?></h1>

<?php
	if(@$_GET["act"]	==	"Recovery")
		{
?>
<p>
<form id="formRecovery" name="formRecovery" method="post" action="azioni.php?act=recuperaPassword">
  <table width="600" align="center">
    <tr>
      <td align="left" valign="middle"><h2>Ho smarrito la password</h2></td>
    </tr>
    <tr>
      <td height="24" align="center" valign="middle">Per recuperare la password è necessario inserire l'e-mail collegata all'account in questione</td>
    </tr>
    <tr>
      <td height="24" align="center" valign="middle"><input name="email" class="stileCampiInput" id="email" /></td>
    </tr>
    <tr>
      <td height="24" align="center" valign="middle">
        <input type="submit" name="button" id="button" value="Invia" />
      </td>
    </tr>
    <tr>
      <td height="24" align="center" valign="middle"><a href="index.php" title="Torna alla pagina di log-in">Torna alla pagina di log-in</a></td>
    </tr>
    <tr>
      <td height="24" align="center" valign="middle"><a href="registrazione.php" title="Registrati nuovo utente">Registrati  nuovo utente</a></td>
    </tr>
  </table>
</form>
</p>
<?php
		}
	else if(@$_GET["act"]	==	"RegistrazioneOn")
		{
?>
<p>
  <table width="600" align="center">
    <tr>
      <td align="left" valign="middle"><h2>Registrazione avvenuta</h2></td>
    </tr>
    <tr>
      <td height="50" align="left" valign="middle">Registrazione effettutata con successo; a breve riceverai una e-mail con un link per attivare la registrazione.</td>
    </tr>
    <tr>
      <td height="24" align="center" valign="middle"><a href="index.php" title="Torna alla pagina di log-in">Torna alla pagina di log-in</a><a href="recuperPassword.php" title="Ho smarrito la password"></a></td>
    </tr>
  </table>
</p>
<?php
		}
	else if(@$_GET["act"]	==	"AttivazioneOn")
		{
?>
<p>
<table width="600" align="center">
    <tr>
      <td align="left" valign="middle"><h2>Attivazione account</h2></td>
    </tr>
    <tr>
      <td height="50" align="left" valign="middle">La procedura di registrazione si è conclusa con successo, ora puoi effettuare il log-in.</td>
    </tr>
    <tr>
      <td height="24" align="center" valign="middle"><a href="index.php" title="Torna alla pagina di log-in">Vai alla pagina di log-in</a><a href="recuperPassword.php" title="Ho smarrito la password"></a></td>
    </tr>
</table>
</p>
<?php
		}
	else if(@$_GET["act"]	==	"RecuperoOn")
		{
?>
<p>
<table width="600" align="center">
    <tr>
      <td align="left" valign="middle"><h2>Recupero password</h2></td>
    </tr>
    <tr>
      <td height="50" align="left" valign="middle">Ti è stata inviata una e-mail con i dati di accesso.</td>
    </tr>
    <tr>
      <td height="24" align="center" valign="middle"><a href="index.php" title="Torna alla pagina di log-in">Vai alla pagina di log-in</a><a href="recuperPassword.php" title="Ho smarrito la password"></a></td>
    </tr>
</table>
</p>
<?php
		}
	else if(@$_GET["act"]	==	"RecuperoOff")
		{
?>
<p>
<table width="600" align="center">
    <tr>
      <td align="left" valign="middle"><h2>Recupero password</h2></td>
    </tr>
    <tr>
      <td height="50" align="left" valign="middle">L'e-mail indicata non corrisponde ad alcun account attivo.</td>
    </tr>
    <tr>
      <td height="24" align="center" valign="middle"><a href="index.php?act=Recovery" title="Ho smarrito la password">Ho smarrito la password</a></td>
    </tr>
</table>
</p>
<?php
		}
	else
		{
?>
<p>
<form id="formLogin" name="formLogin" method="post" action="azioni.php?act=login">
  <table width="600" align="center">
    <tr>
      <td colspan="2" align="left" valign="middle"><h2>Effettua il Log-in</h2></td>
    </tr>
    <tr>
      <td width="150" height="24" align="left" valign="middle">Username:</td>
      <td width="450" height="24" align="left" valign="middle">
        <input name="username" type="text" class="stileCampiInput" id="username" />
      </td>
    </tr>
    <tr>
      <td height="24" align="left" valign="middle">Password:</td>
      <td height="11" align="left" valign="middle"><input name="password" type="password" class="stileCampiInput" id="password" /></td>
    </tr>
    <tr>
      <td height="24" colspan="2" align="left" valign="middle"><label>
        <input name="ricorda" type="checkbox" id="ricorda" value="1" />
      </label>
      Ricorda al prossimo accesso</td>
    </tr>
    <tr>
      <td height="24" colspan="2" align="center" valign="middle">
        <input type="submit" name="button" id="button" value="Invia" />
      </td>
    </tr>
    <tr>
      <td height="24" colspan="2" align="center" valign="middle"><a href="index.php?act=Recovery" title="Ho smarrito la password">Ho smarrito la password</a></td>
    </tr>
    <tr>
      <td height="24" colspan="2" align="center" valign="middle"><a href="registrazione.php" title="Registrati nuovo utente">Registrati  nuovo utente</a><a href="#"></a></td>
    </tr>
  </table>
</form>
</p>
<?php
		}
?>
</body>
</html>

