<?php


	if(isset($_COOKIE['LOGIN']))
		{
			$COD_UTENTE =	$_COOKIE['LOGIN'];
		}
	else
		{
			$COD_UTENTE =	0;
			header("Location: index.php");
		}

include "db_connection.php";
print  "
<head><title>Sensor settings</title>
<meta name=\"apple-mobile-web-app-capable\" content=\"yes\">
<link rel=\"apple-touch-icon\" href=\"/icone/app_icon128.png\">
<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\" />

<script type=\"text/javascript\">
	function navigator_Go(url)
	{ window.location.assign(url); }
</script>

<link href=\"stile.css\" rel=\"stylesheet\" type=\"text/css\" />
</head>
<body>
<BR>
<TABLE width=\"100%\"><TR>
<TD align=\"left\"><A href=\"javascript:navigator_Go('index.php');\"><img src=\"icone/left37.png\" width=\"35\"></TD>
</TR></TABLE>
<BR><CENTER>
";

if(isset($_POST['serial']))
// START self_reload
{
$serial=$_POST['serial'];
$device_name=$_POST['device_name'];
$position=$_POST['position'];
$min_ok=$_POST['min_ok'];
$max_ok=$_POST['max_ok'];

if(!isset($_POST['armed']))
{ $armed=0; }
else
{ $armed=1; }

if ((preg_match("/^[a-zA-Z0-9_ ]+$/", $device_name)) and (preg_match("/^[a-zA-Z0-9_ ]+$/", $position)) and (preg_match("/^-?[0-9]{1,3}+$/", $min_ok)) and (preg_match("/^-?[0-9]{1,3}+$/", $max_ok)))
{
$sql = "UPDATE devices set device_name='$device_name', position='$position', min_ok='$min_ok', max_ok='$max_ok', armed='$armed' where serial='$serial'";
$result = $conn->query($sql);
}

$serial="";
$device_name="";
$position="";
$min_ok="";
$max_ok="";
$armed="";
}
// END self_reload



$sql = "SELECT serial, device_name, position, min_ok, max_ok, armed FROM devices order by serial";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
$x=0;
while($row = $result->fetch_assoc()) {
   $serial[$x] = $row["serial"];
   $device_name[$x] = $row["device_name"];
   $position[$x] = $row["position"];
   $min_ok[$x] = $row["min_ok"];
   $max_ok[$x] = $row["max_ok"];
   $armed[$x] = $row["armed"];
   ++$x;
   }

echo "<table class=\"gridtable\">\n";
echo "<tr><th>Serial</th><th>Device</th><th>Position</th>";
echo "<th>Min</th><th>Max</th><th>Armed</th></tr>\n";

for($x=0;$x<$result->num_rows;$x++) {
echo "<form action =\"" . $_SERVER['PHP_SELF'] . "\" method=\"POST\">";
echo "<TR>";
echo "<TD>" . $serial[$x] . "</TD>\n";
echo "<input type=\"hidden\" name=\"serial\" value=\"" . $serial[$x] . "\">\n";
echo "<TD><input type=\"text\" class=\"stileCampiInput\" name=\"device_name\" value=\"" . $device_name[$x] . "\" size=15 onchange=\"this.form.submit()\"></TD>\n";
echo "<TD><input type=\"text\" class=\"stileCampiInput\" name=\"position\" value=\"" . $position[$x] . "\" size=15 onchange=\"this.form.submit()\"></TD>\n";
echo "<TD><input type=\"text\" class=\"stileCampiInput\" name=\"min_ok\" value=\"" . $min_ok[$x] . "\" size=3 onchange=\"this.form.submit()\"></TD>\n";
echo "<TD><input type=\"text\" class=\"stileCampiInput\" name=\"max_ok\" value=\"" . $max_ok[$x] . "\" size=3 onchange=\"this.form.submit()\"></TD>\n";

if ($armed[$x] == 1)
{
echo "<TD><input name=\"armed\" type=checkbox value=\"1\" checked=\"checked\" onchange=\"this.form.submit()\"></TD>\n";
// echo "<TD><input name=\"armed\" value = \"" . $armed[$x] . "\" size=2 onchange=\"this.form.submit()\"></TD>\n";
}
else
{
echo "<TD><input name=\"armed\" type=checkbox value=\"1\" onchange=\"this.form.submit()\"></TD>\n";
}

echo "</TR>\n";
echo "</form>\n";
}
echo "</table>\n";
echo "</body>\n";



} else {
    echo "0 results";
}

$conn->close();
?>
