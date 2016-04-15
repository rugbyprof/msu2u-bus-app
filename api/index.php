<?php

//curl -H "Content-Type: application/json" -X POST https://msu2u.us/bus/api/user/ -d '{"id": 99,"fname": "Terry","lname": "Griffin","user_type": "1","current_lat": "","current_lon": "","timestamp": "0"}' 
//curl -H "Content-Type: application/json" -X POST https://msu2u.us/bus/api/user/ -d 'id=99&fname=Terry&lname=Griffin&user_type=1&current_lat=0.0&current_lon=0.0&timestamp=0' 
//curl -H "Content-Type: application/json" -X POST https://msu2u.us/bus/api/user/ -F 'id=99&fname=Terry' -F 'lname=Griffin' -F 'user_type=1' -F 'current_lat=0.0' -F 'current_lon=0.0' -F 'timestamp=0' 

/****************************************************************************************************
* Configuration
****************************************************************************************************/

require './vendor/autoload.php';

date_default_timezone_set("America/Chicago");

$endPoints = new EndPoints();

$app = new \Slim\App();

$container = $app->getContainer();

//Inject the "database connection into slim
$container['db'] = function ($c) {
	//Credentials stored in db_credentials.json 
	$cred = json_decode(file_get_contents('./db_credentials.json'),true);
    $db = $c['settings']['db'];
    $pdo = new PDO("mysql:host={$cred['host']};dbname={$cred['dbname']};charset=utf8mb4", $cred['user'], $cred['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    return $pdo;
};

//Add a logger to slim
$container['logger'] = function($c) {
    $logger = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler("./logs/app.log");
    $logger->pushHandler($file_handler);
    return $logger;
};


/****************************************************************************************************
* Routes / Controllers
* 	Controllers connect MODELS and VIEWS. 
****************************************************************************************************/

//Code to get user ip address
$checkProxyHeaders = true; // Note: Never trust the IP address for security processes!
$trustedProxies = ['10.0.0.1', '10.0.0.2']; // Note: Never trust the IP address for security processes!
$app->add(new RKA\Middleware\IpAddress($checkProxyHeaders, $trustedProxies));

/**
* @Route: /
* @Description: base endpoint
* @Example: curl -X GET https://msu2u.us/bus/api/
*/
$app->get('/', function ($request, $response, $args) {
	global $endPoints;
	
    $ipAddress = $request->getAttribute('ip_address');

    return $response->write(stripslashes($endPoints->dump()));
});



/**
* @Route: /user/
* @Description: Gets all users.
* @Example: curl -X GET https://msu2u.us/bus/api/user/ 
*/
$endPoints->add('GET','/user/');
$app->get('/user/',function($request, $response, $args){

	$um = new UserModel($this->db);
	
	$results = $um->getAllUsers();

	return $response->write(json_encode($results));
});

/**
* @Route: /user/
* @Description: Gets a single user.
* @Example: curl -X GET https://msu2u.us/bus/api/user/2
*/
$endPoints->add('GET','/user/{id}');
$app->get('/user/{id}',function($request, $response, $args){
	$um = new UserModel($this->db);
	
	$results = $um->getUser($args['id']);

	return $response->write(json_encode($results));
});

/**
* @Route: /user/
* @Description: Adds a single user.
* @Example: curl -H "Content-Type: application/json" -X POST https://msu2u.us/bus/api/user/ -d '{"fname": "Joe","lname": "Bob","user_type": "1","current_lat": "33.123","current_lon": "98.3434"}' 
*/
$app->post('/user/', function ($request, $response, $args) {

	$log = new ErrorHelp("./logs/error.log");
	$data = $request->getParsedBody();
	$log->message(print_r($data,true));
	$um = new UserModel($this->db);
	$success = $um->addUser($data);
	return $response->write(json_encode($success));
});



// Run app
$app->run();

/****************************************************************************************************
* Models
* 	A model is the name given to the permanent storage of the data used in the overall design. It must allow 
* 	access for the data to be viewed, or collected and written to, and is the bridge between the View 
* 	component and the Controller component in the overall pattern.
****************************************************************************************************/


/**
* @Class: UserModel
* @Description: 
*	This interfaces with the user table, and performs any necessary actions to 
*	CREATE,EDIT,UPDATE,or DELETE users.
* @Methods:
*		
*/
class UserModel{
	function __construct($db){
		$this->db = $db;
	}
	
	function getAllUsers(){
		$stmt = $this->db->query('SELECT * FROM users');
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $results;
	}
	
	function getUser($id){
		$stmt = $this->db->query("SELECT * FROM users WHERE id = '{$id}'");
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $results;
	}
	
	function addUser($data){
		$data['timestamp'] = time();
		$data['id'] = $this->getNextId();
		$keys = "`".implode("`,`",array_keys($data))."`";
		$vals = "'".implode("','",array_values($data))."'";
		
		
		$query = "INSERT INTO users ({$keys}) 
				  VALUES ({$vals})";
				  	    
		$affected_rows = $this->db->exec($query);

		return ["success"=>($affected_rows > 0)];

	}
	
	function getNextId(){
		$stmt = $this->db->query('SELECT max(id) as max FROM users');
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		
		$log = new ErrorHelp("./logs/error.log");
	    $log->message(print_r($results,true));
	    
		return $results[0]['max']+1;		
	}
}

class EndPoints{
	function __construct(){
		$this->uris = [];
	}
	
	function add($category,$uri){
		$this->uris[$category][] = array('uri'=>$uri);
	}
	
	function dump(){
		return json_encode($this->uris);
	}
}

class ErrorHelp{
	function __construct($path="./error.log"){
		$this->path = $path;
	}
	
	function message($error){
	
		file_put_contents($this->path,date("H:i:s D/M/Y",time())."\n",FILE_APPEND);
		file_put_contents($this->path,$error,FILE_APPEND);
	}
}

function dd($foo){
	print_r($foo);
	echo"<br>";
}