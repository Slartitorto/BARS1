<?php

include "db_connection.php";

	if(isset($_COOKIE['LOGIN']))
		{
			$COD_UTENTE =	$_COOKIE['LOGIN'];
		}
	else
		{
			$COD_UTENTE =	0;
			header("Location: index.php");
		}


// $Sql	= "SELECT `codUtente`, `username` FROM `utenti` WHERE `codUtente`='".$COD_UTENTE."';";
// $result	= $conn_query($Sql);
// $Dati 	= mysql_fetch_array($result);

?>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo NOMESITO; ?></title>
<link href="stile.css" rel="stylesheet" type="text/css" />
</head>

<body>

<?php

$serial=($_GET["serial"]);
$last=($_GET["last"]);
if ( $last == 2)
	{
	$next_last = 7;
	$string_last = "ultima settimana";
	}
else if ( $last == 7)
	{
	$next_last = 30;
	$string_last = "ultimo mese";
	}

// SELECT for data to graph
$sql = "SELECT unix_timestamp(timestamp) as timestamp, data FROM rec_data where serial = '$serial' and timestamp > now()- interval '$last'  day order by timestamp";
$result = $conn->query($sql);
while ($row = $result->fetch_array()) {
$timestamp = $row['timestamp'];
$timestamp *=1000;
$data = $row['data'];

   $data1[] = "[$timestamp, $data]";
}


print  "<head><title>Sensor details</title>
	<meta name=\"apple-mobile-web-app-capable\" content=\"yes\">
	<link rel=\"apple-touch-icon\" href=\"/icone/app_icon128.png\">
	<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\" />
	<script type=\"text/javascript\">
                function navigator_Go(url) {
                               window.location.assign(url);
                }
        </script>
	<script src=\"scripts/jquery.min.js\"></script>
	<script src=\"scripts/highcharts.js\"></script>
	<script>
	$(function () {
		Highcharts.setOptions({
        		global: {
            			timezoneOffset: -60
        		}
    		});
    	$('#container1').highcharts({
        	chart: {
            		type: 'line'
        	},
        	title: {
             	   text: ''
            	},
		legend: {
            		enabled: false
        	},
		xAxis: {
            		type: 'datetime',
            	},
		yAxis: {
           		title: {
                		text: 'Temperature (C)'
           			},
        		},
        	series: [{
			data: [";
echo join($data1, ',') ;
print			"]
   		     }]
 	 	  });
		});
	</script>

	<style type=\"text/css\">
		table.gridtable {
		font-family: avenir,arial,sans-serif;
		color:#000000;
		border-width: 0px;
		border-color: #666666;
		border-collapse: collapse;
	}
	table.gridtable th {
		border-width: 0px;
		text-align: center;
		font-size:16;
		padding: 8px;
		border-style: solid;
		border-color: #666666;
		background-color: #efefef;
	}
	table.gridtable td {
		border-width: 0px;
		padding: 8px;
		font-size:13;
		text-align: center;
		border-style: solid;
		border-color: #666666;
		background-color: #ffffff;
	}
	</style>
	<BR>
	<TABLE width=\"100%\"><TR>
	<TD align=\"left\" width=\"90%\">
	<A href=\"javascript:navigator_Go('index.php');\"><img src=\"icone/left37.png\" width=\"35\"></A></TD>
	<TD align=\"right\">
	<A href=\"javascript:navigator_Go('device_details.php?serial=$serial&last=$last');\"><img src=\"icone/refresh57.png\" width=\"30\">
	</TD>
	</TR></TABLE>
	<BR><CENTER>
	";
function format_time($t,$f=':') // t = seconds, f = separator
{
  return sprintf("%3d%s%02d", ($t/60) , $f, $t%60);
}
$sql = "SELECT device_name, position, batt_type FROM devices where serial = '$serial'" ;
$result = $conn->query($sql);
if ($result->num_rows > 0) {
while($row = $result->fetch_assoc()) {

$device_name = $row["device_name"];
$position = $row["position"];
$batt_type = $row["batt_type"];

    }
 }

// SELECT last record
$sql = "SELECT timestamp, data, battery,period, timestampdiff(second,timestamp,now()) as sec_delay FROM rec_data where serial = '$serial' order by timestamp desc limit 1";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {

$time_stamp = $row["timestamp"];
$temp = $row["data"];
$batt = $row["battery"];
$period = $row["period"];
$min_period = format_time($period);
$sec_delay=$row["sec_delay"];
$min_delay=format_time($sec_delay);

    }
if ($batt_type == "nimh") {
	$perc_batt = intval((($batt - 2.9)*77));
	}
else if ($batt_type == "litio") {
        $perc_batt = intval((($batt - 2.7)*200));
        }

// SELECT last counter
$query = "select counter from rec_data where serial = '$serial' order by timestamp desc limit 1";
$result = $conn->query($query);
while($row = $result->fetch_assoc()) {
$link_qlt0=$row["counter"];
}
  // SELECT last counter -10
$query = "select counter from rec_data where serial = '$serial' order by timestamp desc limit 10,1";
$result = $conn->query($query);
while($row = $result->fetch_assoc()) {
$link_qlt1=$row["counter"];
        }
$link_qlt = intval(1000/($link_qlt0 - $link_qlt1));

echo " <table class=\"gridtable\">	";
echo " <tr><th>" . $device_name . "</th><th>" . $position . "</th><th>Temp: " . $temp . "&deg C</th></tr>";
echo " <TR><TD>Serial: <B> " . $serial . "</B></TD><TD></TD><TD>Batteria:  <B>" . $batt . "</B> (" . $perc_batt . "%) - " . $batt_type . "</TD></TR>";
echo " <TR><TD colspan=2>Periodo di rilevazione (min.)<B>" . $min_period . "</B><TD>Ultimo aggiornamento: <B>" . $min_delay . "</B></TD></TR>";
echo " <TR><TD colspan=3>Link quality: " . $link_qlt . "%</TD></TR>";

print "
	</table><br><br><br>
	<div id=\"container1\" style=\"width:100%; height:400px;\"></div>
";
echo "<A href=\"javascript:navigator_Go('device_details.php?serial=$serial&last=$next_last');\">" . $string_last . "</a>";
} else {
    echo "0 results";
}

$conn->close();
?>
