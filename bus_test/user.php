<?php
	//Format some of the data
	$_POST['pass'] = sha1($_POST['pass']);
	$_POST['id'] = ucfirst ($_POST['id']);
	
	//Curl begins to check on similar IDs
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_URL, "http://msu2u.us/bus/api/v1/users/" . $_POST['id']);

	$result=curl_exec($ch);
	curl_close($ch);

	$decode = json_decode($result, true);

	if ($result == NULL)
		header('Location: ' . $_SERVER['HTTP_REFERER']);
	else if ($decode['data'][0]['pass'] == $_POST['pass'])
		header("Location: https://www.youtube.com/watch?v=dQw4w9WgXcQ");
	else
		header("Location: http://www.google.com/");
	exit;
?>