<?php
error_reporting(0);

$link = mysqli_connect('localhost', 'yam', 'yams', 'msu2u');
if($link == false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}


$lat = $_POST['lat'];
$lon = $_POST['lon'];
$time = $_POST['time'];

$command = $_POST['command'];
$sql = "UPDATE users SET current_lat = $lat , current_lon = $lon , timestamp = '$time' WHERE id = 4";

if($link->query($sql)=== TRUE) {echo "record updated";}
else {echo "error updating" . $link->error;}

switch($command){
    case 'driver_location': 
        $json = update_driver_location($lat,$lon,$time); 
        break;
    default:
        $json = array('error'=>'command not found');
}
echo json_encode($json);

function update_driver_location($lat,$lon,$time)
{
    return array('latitude,longitude'=>$lat,$lon,$time);
}

mysqli_close();
?>

