<?php

$connect = mysqli_connect("localhost", "msu2u", "msu2u2016!!!!!", "msu2u");

if (!$connect) {
    echo "Unable to connect to database." . PHP_EOL;
    exit;
}

$fname = $_POST['fname'];
$lname = $_POST['lname'];
$mnumber = $_POST['mnumber'];
$password = $_POST['password'];
$password2 = $_POST['password_match'];
$email = $_POST['email'];
	
if ($password == $password2)
{
	$enc_pass = sha1($password);
	mysqli_query($connect,"INSERT INTO users (fname,lname,id,pass,email, user_type) VALUES ('{$fname}','{$lname}','{$mnumber}','{$enc_pass}','{$email}', '1')");
	header("Location: index.html"); 
	exit;
}
else
{
	echo "Passwords do not match.";
    exit;
}
	
?>