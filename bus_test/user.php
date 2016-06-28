<?php

$url = "http://msu2u.us/bus/api/v1/users/";

$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

// Will return the response, if false it print the response
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

curl_setopt($ch, CURLOPT_URL, $url);
$result=curl_exec($ch);
curl_close($ch);

// Dump JSON
var_dump(json_decode($result, true));
?>