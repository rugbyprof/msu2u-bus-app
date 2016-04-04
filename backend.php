<?php
error_reporting(0);

$link = mysqli_connect("localhost", "msu2u", "msu2u2016!!!!!", "msu2u");

$lat = $_POST['lat'];
$lon = $_POST['lon'];
$time = $_POST['time'];

$command = $_POST['command'];

switch($command){
    case 'driver_location': 
        $json = update_driver_location($lat,$lon,$time); 
        break;
    default:
        $json = array('error'=>'command not found');
}


function update_driver_location($link,$lat,$lon,$time){

}
