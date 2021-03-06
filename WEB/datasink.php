
<?php
include "db_connection.php";

if(isset($_GET['data'])){
  $data=$_GET['data'];
  $router=$_GET['router'];
} else exit();

$result = mysqli_query($conn,"select current_key from router where router = ".$router);
$row = mysqli_fetch_array($result);
$key = $row[0];
echo $key . "\n";
$strarr = str_split($data);
$keyarr = str_split($key);
$len = strlen($data);
for($i=0; $i<$len; $i++) {
  $newstr[$i] = chr(ord($strarr[$i]) - ord($keyarr[$i]) + 30);
}
$dec_string = implode("",$newstr);
list($data_type,$serial,$counter,$data,$battery,$period) = explode(":",$dec_string);
if($period==NULL) $period=300;

// Da provare
// if ((substr_count($dec_string, ":") < 5) or (substr_count($dec_string, ":") > 6)) exit();

$data = intval($data)/100;
$battery = intval($battery)/1000;

$query = "DELETE from last_rec_data where serial = '$serial'";
// echo $query . "\n";
$result = mysqli_query($conn,$query);

$query = "INSERT INTO last_rec_data (data_type,serial,counter,data,battery,period,router) VALUES('$data_type','$serial','$counter','$data','$battery','$period','$router')";
// echo $query  . "\n";
$result = mysqli_query($conn,$query);

$query = "INSERT INTO rec_data (data_type,serial,counter,data,battery,period,router) VALUES('$data_type','$serial','$counter','$data','$battery','$period','$router')";
//echo $query  . "\n";
$result = mysqli_query($conn,$query);

$query = "SELECT armed, batt_alarmed, alarmed, min_ok, max_ok, device_name, position, tenant from devices where serial = '$serial'";
// echo $query . "\n";
$result = mysqli_query($conn,$query);
$row = mysqli_fetch_array($result);

$armed = $row[0];
$batt_alarmed = $row[1];
$alarmed = $row[2];
$min_ok = intval($row[3]);
$max_ok = intval($row[4]);
$device_name = $row[5];
$position = $row[6];
$tenant = $row[7];

/*
echo "data_type = $data_type \n";
echo "serial = $serial \n";
echo "counter = $counter \n";
echo "data = $data \n";
echo "battery = $battery \n";
echo "period = $period \n";
echo "armed = $armed \n";
echo "alarmed = $alarmed \n";
echo "min_ok = $min_ok \n";
echo "max_ok = $max_ok \n";
echo "device_name = $device_name \n";
echo "position = $position \n";
echo "tenant = $tenant \n";
*/

if (($data < $min_ok) or ($data > $max_ok)) {
  // alarm condition half
  if (($armed == 1) and ($alarmed == 0)) {
    //alarm condition full
    $query = "update devices set alarmed = 1 where serial = '$serial'";
    //echo $query . "\n";
    $result = mysqli_query($conn,$query);

    $subject = "Allarme $device_name $position";
    $message = "Temperatura rilevata = $data - out of range (min = $min_ok - max = $max_ok)";
    $headers = "From: root@slartitorto.eu \r\n" .
    "Reply-To: root@slartitorto.eu \r\n";

    $query = "select email from utenti where t0 = '$tenant' or t1 = '$tenant' or t2 = '$tenant' or t3 = '$tenant'";
    //echo $query . "\n";
    $result = mysqli_query($conn,$query);
    while (($row = mysqli_fetch_row($result))) {
      $to = $row[0];
      mail($to, $subject, $message, $headers);

      // CURL pushbullett (see https://wiki.onion.io/Tutorials/PHP-PushBullet-Example)
      $authToken = "YOUR_PUSHBULLETT_TOKEN";
      $curl = curl_init('https://api.pushbullet.com/v2/pushes');
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_HTTPHEADER, ["Authorization: Bearer $authToken"]);
      curl_setopt($curl, CURLOPT_POSTFIELDS, [
        "type" => "note",
        "email" => "$to",
        "title" => "Allarme $device_name $position",
        "body" => "Temperatura rilevata = $data - out of range (min = $min_ok - max = $max_ok)"]
      );
      curl_exec($curl);
      curl_close($curl);

    }
  }
} else {
  // If previously alarmed, reset alarm flag
  if ($alarmed == 1) {
    $query = "update devices set alarmed = 0 where serial = '$serial'";
    $result = mysqli_query($conn,$query);
  }
}
mysqli_close($conn);
?>
