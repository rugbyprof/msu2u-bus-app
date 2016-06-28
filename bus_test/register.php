<?php
	//Format some of the data
	$_POST['pass'] = sha1($_POST['pass']);
	$_POST['user_type'] = "1";
	$_POST['id'] = ucfirst ($_POST['id']);
	$_POST['fname'] = ucfirst ($_POST['fname']);
	$_POST['lname'] = ucfirst ($_POST['lname']);
	unset($_POST['pass_match']);
	
	//Curl begins to check on similar IDs
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_URL, "http://msu2u.us/bus/api/v1/users/" . $_POST['id']);

	$result=curl_exec($ch);
	curl_close($ch);

	if ($result != NULL)
	{
		header("Location: http://www.amazon.com/");
		exit;
	}

	//Curl begins to add user
	$url = "http://msu2u.us/bus/api/v1/users/";
    $curl = curl_init($url);	
	
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $_POST);
	
	$curl_response = curl_exec($curl);

	if ($curl_response == false) {
		$info = curl_getinfo($curl);
		curl_close($curl);
		die(var_export($info));
	}
	curl_close($curl);
			
	$decoded = json_decode($curl_response);
	if (isset($decoded->response->status) && $decoded->response->status == 'ERROR') {
		die('error occured: ' . $decoded->response->errormessage);
	}
	
	header("Location: index.html"); 
?>