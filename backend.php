<?php
//error_reporting(E_ALL);
error_reporting(0);
$link = mysqli_connect("localhost", "msu2u2016", "Ub2v9AMsCNRwLKCc", "msu2u2016");

if (!$link) {
    echo "Error: Unable to connect to MySQL." . PHP_EOL;
    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
    echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
    exit;
}

$response = null;
$error = null;

if(array_key_exists('command',$_POST)){
	$command = $_POST['command'];
}else{
	$command = "bad";
	$error = "No command to execute!";
}

switch($command){
	case 'insert':
		$json = insertPerson($link,$_POST['name'],$_POST['email'],$_POST['phone_number'],$_POST['message']);
		break;
	case 'delete':
		break;
	case 'update':
		break;
	default:
		$json = array('error'=>"Command not found");
}

function insertPerson($link,$name,$phone,$message,$email){
	$id = 1;
	list($first,$last) = explode(' ',$name);
	if(mysqli_query($link,"INSERT INTO users (id,first,last,email,phone_number,message) VALUES ('{$id}','{$first}','{$last}','{$email}','{$phone}','{$message}')")){
		return array('response'=>"everything good");
	}else{
		return array('error'=>"oops");
	}
}

//$response = array('response'=>'Good Job');
echo json_encode($json); 
?>
