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
?>

<head>
<title>Home Sensors</title>
<link href="stile.css" rel="stylesheet" type="text/css" />
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<link rel="apple-touch-icon" href="/icone/temp_icon.png">
<meta name="apple-mobile-web-app-status-bar-style" content="default" />
<SCRIPT type="text/javascript">
        function navigator_Go(url) {
                window.location.assign(url);
        }
</SCRIPT>
</head>
<body>
<BR>
<TABLE width="100%"><TR>
<TD align="left"><A href="javascript:navigator_Go('device_settings.php');"><img src="icone/very-basic-settings-icon.png" width="40"></A></TD>
<TD align="right"><A href="javascript:navigator_Go('index.php');"><img src="icone/refresh57.png" width="30"></A></TD>
</TR></TABLE>
<BR><BR><CENTER>

<?php

include "db_connection.php";

$query = "SELECT idUtente,t0,t1,t2,t3 FROM utenti WHERE codUtente='$COD_UTENTE'";
$result = $conn->query($query);
while($row = $result->fetch_assoc()) {
	$idUtente = $row["idUtente"];
	$tenant0 = $row["t0"];
  $tenant1 = $row["t1"];
  $tenant2 = $row["t2"];
  $tenant3 = $row["t3"];
}

$query = "SELECT serial, device_name, position, batt_type, min_ok, max_ok FROM devices where tenant in ($tenant0,$tenant1,$tenant2,$tenant3)";
$result = $conn->query($query);
$x=0;
while($row = $result->fetch_assoc()) {
	$serial[$x]=$row["serial"];
	$device_name[$x]=$row["device_name"];
	$position[$x]=$row["position"];
	$batt_type[$x]=$row["batt_type"];
	$min_ok[$x]=$row["min_ok"];
	$max_ok[$x]=$row["max_ok"];
	++$x;
}

$count=count($serial);
for($i=0;$i<$count;$i++) {
	$query = "select data, counter, battery, timestampdiff(second,timestamp,now()) as sec_delay from rec_data where serial = '$serial[$i]' order by timestamp desc limit 1";
	$result = $conn->query($query);
	while($row = $result->fetch_assoc()) {
    $last_data[$i]=$row["data"];
    $sec_delay[$i]=$row["sec_delay"];
    $battery[$i]=$row["battery"];
	  $link_qlt0[$i]=$row["counter"];
	}
}
for($i=0;$i<$count;$i++) {
  // SELECT last counter -100
$query = "select counter from rec_data where serial = '$serial[$i]' order by timestamp desc limit 100,1";
$result = $conn->query($query);
while($row = $result->fetch_assoc()) {
$link_qlt1=$row["counter"];
        }
$link_qlt[$i] = intval(10000/($link_qlt0[$i] - $link_qlt1));
}

for($i=0;$i<$count;$i++) {
        if (($batt_type[$i] == "litio" and $battery[$i] < 2.7) or ($batt_type[$i] == "nimh" and $battery[$i] < 3.2)) {
                $warn[$i] = "battery_low";
        }
	else if ($sec_delay[$i] > 1000 or $link_qlt[$i] < 80) {
                $warn[$i] = "link";
        }
	else if ($last_data[$i] < $min_ok[$i] or $last_data[$i] > $max_ok[$i]) {
		$warn[$i] = "red";
        }
	else {
		$warn[$i] = "green";
	}
}

print "<table class=\"gridtable\"><tr><th>Termometro</th><th>Posizione</th><th>Temp</th><th>Status</th></tr> ";
	for($i=0;$i<$count;$i++) {
	echo "<TR>";
	echo "<TD><A HREF=\"javascript:navigator_Go('device_details.php?serial=";
        echo  $serial[$i] . "&last=2');\">" . $device_name[$i]. "</A></TD><TD>" . $position[$i] . "</TD>";
        echo "<TD>" . $last_data[$i] . "</TD>";
	echo "<TD><img src=\"icone/" . $warn[$i] . "_signal.png\" width=\"25\"></TD>";
	}
	echo "</TR>";
	echo "</TABLE> ";

$conn->close();
?>
