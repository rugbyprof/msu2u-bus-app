<?php

// Holds all the packages we installed with composer
require './vendor/autoload.php';

use \Firebase\JWT\JWT;

// Have to set the timezone else php cries like a little bitch.
date_default_timezone_set("America/Chicago");

/****************************************************************************************************
* Routes
****************************************************************************************************/

$app = new \Slim\App();

//curl -X GET https://msu2u.us/bus/api/v1/ --user root:t00r
// $app->add(new \Slim\Middleware\HttpBasicAuthentication([
//     "path" => "/v1", /* or ["/admin", "/api"] */
//     "realm" => "Protected",
//     "users" => [
//         "root" => "t00r",
//         "user" => "passw0rd"
//     ],
//     "callback" => function ($request, $response, $arguments) {
//         print_r($arguments);
//     },
// 	"error" => function ($request, $response, $arguments) {
//         $data = [];
//         $data["status"] = "error";
//         $data["message"] = $arguments["message"];
//         return $response->write(json_encode($data, JSON_UNESCAPED_SLASHES));
//     }
// ]));



$app->group('/v1', function () use ($app) {
	$app->get('/','base');
    $app->get('/user/', '\UserController:getUsers');
    $app->get('/user/{id:[0-9]+}', '\UserController:getUser');
    $app->get('/menu/', '\MenuController:getMenus');
    $app->get('/menu/{id}', '\MenuController:getMenuItems');
    $app->post('/user/', '\UserController:addUser');
    $app->post('/menu/', '\MenuController:createMenu');
    $app->post('/menu/{id}', '\MenuController:addMenuItem');
    $app->put('/user/{id}', '\UserController:updateUser');
    $app->delete('/user/{id}', '\UserController:deleteUser');
    $app->delete('/menu/{menuId}[/{itemId}]', '\MenuController:deleteMenu');
});

$app->run();


/**
* @Route: /user/
* @Description: Gets all users.
* @Example: curl -X GET https://msu2u.us/bus/api/v1/user/ 
*/
function base ($request, $response, $args) {
	$key = "supersecretkey";
	$token = array(
		"iss" => "http://msu2u.us",
		"aud" => "http://msu2u.us",
		"iat" => 1356999524,
		"nbf" => 1357000000,
		"scopes"=> ["menu", "user"] 
	);
	
	echo"<pre>";

	/**
	 * IMPORTANT:
	 * You must specify supported algorithms for your application. See
	 * https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40
	 * for a list of spec-compliant algorithms.
	 */
	$jwt = JWT::encode($token, $key);
	
	print_r($jwt);
	
	$decoded = JWT::decode($jwt, $key, array('HS256'));


	/*
	 NOTE: This will now be an object instead of an associative array. To get
	 an associative array, you will need to cast it as such:
	*/

	$decoded_array = (array) $decoded;
	
	print_r($decoded);


	/**
	 * You can add a leeway to account for when there is a clock skew times between
	 * the signing and verifying servers. It is recommended that this leeway should
	 * not be bigger than a few minutes.
	 *
	 * Source: http://self-issued.info/docs/draft-ietf-oauth-json-web-token.html#nbfDef
	 */
	JWT::$leeway = 60; // $leeway in seconds
	$decoded = JWT::decode($jwt, $key, array('HS256'));
	
	print_r((array)$decoded);
	
	
}

/****************************************************************************************************
* User Controllers
****************************************************************************************************/


class UserController{

	var $um;
	
	function __construct(){
		$this->um = new UserModel();
	}

	/**
	* @Route: /user/
	* @Description: Gets all users.
	* @Example: curl -X GET https://msu2u.us/bus/api/v1/user/ 
	*/
	public function getUsers ($request, $response, $args) {

		return $this->sendResponse($response,$this->um->getUsers());

	}

	/**
	* @Route: /user/
	* @Description: Gets a single user.
	* @Example: curl -X GET https://msu2u.us/bus/api/v1/user/{id}
	*/
	public function getUser ($request, $response, $args) {

		return $this->sendResponse($response,$this->um->getUser($args['id']));

	}

	/**
	* @Route: /user/
	* @Description: Adds a single user.
	* @Example: curl -H "Content-Type: application/json" -X POST https://msu2u.us/bus/api/v1/user/ -d '{"fname": "Joe","lname": "Bob","user_type": "1","current_lat": "33.123","current_lon": "98.3434"}' 
	*/
	public function addUser ($request, $response, $args) {

		//Get the posted data from the request
		$data = $request->getParsedBody();
		
		//Add the user and send the response
		return $this->sendResponse($response,$this->um->addUser($data));
		
	}

	/**
	* @Route: /user/
	* @Description: Adds a single user.
	* @Example: curl -H "Content-Type: application/json" -X PUT https://msu2u.us/bus/api/v1/user/{id} -d '{"lname": "Cobby","user_type": "2"}' 
	*            curl -H "Content-Type: application/json" -X PUT https://msu2u.us/bus/api/v1/user/101 -d '{"lname": "Flabby","user_type": "1","current_lat": "33.88878"}'
	*/
	public function updateUser ($request, $response, $args) {

		//Get the data to update the user with
		$data = $request->getParsedBody();
			
		//Add the user and send the response
		return $this->sendResponse($response,$this->um->updateUser($args['id'],$data));
		
	}
	

	/**
	* @Route: /user/
	* @Description: Deletes a single user.
	* @Example: curl -X DELETE https://msu2u.us/bus/api/v1/user/{id}
	*/
	public function deleteUser ($request, $response, $args) {

		$data = $request->getParsedBody();
			
		return $this->sendResponse($response,$this->um->deleteUser($args['id']));
				
	}
	
	private function sendResponse($response,$results){
		return $response->withStatus(200)
			->withHeader('Content-Type', 'application/json')
			->write(json_encode($results));
	}
}



/****************************************************************************************************
* Menu Controllers
****************************************************************************************************/


class MenuController{
	var $mm;
	
	function __construct(){
		$this->mm = new MenuModel();
	}
	
	/**
	* @Route: /menus/
	* @Description: Gets all menus.
	* @Example: curl -X GET https://msu2u.us/bus/api/menus/
	*/
	public function getMenus ($request, $response, $args) {
	
		return $this->sendResponse($response,$this->mm->getMenus());
	
	}

	/**
	* @Route: /menus/
	* @Description: Gets all menus.
	* @Example: curl -X GET https://msu2u.us/bus/api/v1/menus/{id}
	*/
	public function getMenuItems($request, $response, $args) {
	
		return $this->sendResponse($response,$this->mm->getMenuItems($args['id']));
	
	}

	/**
	* @Route: /menus/
	* @Description: Gets all menus.
	* @Example: curl -H "Content-Type: application/json" -X POST https://msu2u.us/bus/api/v1/menu/ -d '{"":""}'
	*/
	public function createMenu($request, $response, $args) {
	
		$data = $request->getParsedBody();
	
		return $this->sendResponse($response,$this->mm->createMenu($data));

	}

	/**
	* @Route: /menus/
	* @Description: Gets all menus.
	* @Example: curl -H "Content-Type: application/json" -X POST https://msu2u.us/bus/api/v1/menu/ -d '{"":""}'
	*/
	public function addMenuItem($request, $response, $args) {
	
		$data = $request->getParsedBody();
	
		return $this->sendResponse($response,$this->mm->addMenuItem($args['id'],$data));
	
	}

	/**
	* @Route: /user/
	* @Description: Deletes a single user.
	* @Example: curl -X DELETE https://msu2u.us/bus/api/v1/user/{id}
	*/
	public function deleteMenu ($request, $response, $args) {
	
		if(!isset($args['itemId'])){
			$args['itemId'] = false;
		}
		
		return $this->sendResponse($response,$this->mm->deleteMenu($args['menuId'],$args['itemId']));
	
	}

	
	private function sendResponse($response,$results){
		return $response->withStatus(200)
			->withHeader('Content-Type', 'application/json')
			->write(json_encode($results));
	}
}




/****************************************************************************************************
* Models
****************************************************************************************************/

/**
* This interfaces with the menus table.
* 
* @Method: array getMenus()
* @Method: array getMenuItems(int)
* @Method: array createMenu(array)
* @Method: array addMenuItem(int,array)
*		
*/
class MenuModel{
  /**
   * @var resource $db  database connection resource
   */
    var $db;
    var $response;        
    
	function __construct(){
		$this->db = new dbManager();
	}
	
  /**
   * Gets all menus.
   * @Return array
   */	
	public function getMenus(){

		return $this->db->fetch('select * from menus');
	}

  /**
   * Gets all menu items for a given menu.
   * @Param int id
   * @Return array
   */	
	public function getMenuItems($id){
	
		return $this->db->fetch('select * from menu_items where menu_id = ?',array($id));
		
	}
	
  /**
   * Adds a new menu to the system
   * @Param array data
   * @Return array
   */	
	public function createMenu($data){
		$data['id'] = $this->db->getNextId('menus','id');
		
		return $this->db->insert('menus',$data);
	}

  /**
   * Adds a new menu item to a specifice menu. It calculates a new item id and a order value.
   * @Param array data
   * @Return array
   */		
	public function addMenuItem($id,$data){
		$data['item_id'] = $this->db->getNextId('menu_items','item_id',"menu_id = {$id}");
		$data['menu_id'] = $id;
		$data['order'] = $data['item_id'] * 10;
		
		return $this->db->insert('menu_items',$data);	
	}
	
  /**
   * Deletes a menu from the menus table along with its items, or just an item from an existing menu.
   * @Param int $id
   * @Return array
   */	
	public function deleteMenu($menuId,$itemId){

		if($itemId === false){
			$one = $this->db->delete('menus',['id'=>$menuId]);
			$two = $this->db->delete('menu_items',['menu_id'=>$menuId]);
			return [$one,$two];
		}else{
			return $this->db->delete('menu_items',[['menu_id'=>$menuId],['item_id'=>$itemId]]);
		}
	}
	
}

/**
* This interfaces with the user table, and performs any necessary actions to 
*	    CREATE,EDIT,UPDATE,or DELETE users.
* 
* @Method: array getUsers()
* @Method: array getUser()
* @Method: array addUser(array)
* @Method: array updateUser()
* @Method: array deleteUser()
*		
*/
class UserModel{
  /**
   * @var resource $db  database connection resource
   */
    var $db;
    var $response;        
    
	function __construct(){
		$this->db = new dbManager();
	}
    
  /**
   * Gets all users from the user table.
   * @Return array
   */
	public function getUsers(){
		return $this->db->fetch('select * from users');
	}
    
  /**
   * Gets a user from the user table based on id 
   * @Param int $id
   * @Return array
   */
	public function getUser($id){
		return $this->db->fetch('select * from users where id = ?',array($id));
	}
    
  /**
   * Adds a user to the user table
   * @Param array $data
   * @Return array
   */	
	public function addUser($data){
		$data['timestamp'] = time();
		$data['id'] = $this->db->getNextId('users','id');
		
		return $this->db->insert('users',$data);
	}
	
  /**
   * Updates a user from the user table by replacing each value present in the data array to the row identified by '$id'.
   * @Param int $id
   * @Param array $data
   * @Return array
   */	
	public function updateUser($id,$data){
		return $this->db->update('users','id',$id,$data);
	}
	
  /**
   * Deletes a user from the user table identified by id.
   * @Param int $id
   * @Return array
   */	
	public function deleteUser($id){
		return $this->db->delete('users',['id'=>$id]);

	}
}


//https://github.com/joshcam/PHP-MySQLi-Database-Class
class dbManager{

	var $response;
	var $db;
	
	function __construct(){

		$this->respone = [];
		
		$cred = json_decode(file_get_contents('./db_credentials.json'),true);

		$this->db = new MysqliDb ($cred['host'], $cred['user'], $cred['pass'], $cred['dbname']);
		
	}
	
	public function fetch($sql,$params=null){
	
		$this->respone = [];

		$rows = $this->db->rawQuery($sql,$params);
		
		if($rows){
			$this->response['success'] = true;
			$this->response['data'] = $rows;
		}else{
    		$this->response['error'] = $db->getLastError();	
		}
		
		return $this->response;
	}
	
	public function insert($table,$data){
		$this->respone = [];
		
		$id = $this->db->insert($table, $data);
		
		
		if($id){
    		$this->response['success'] = true;
		}else{
    		$this->response['error'] = $db->getLastError();	
		}
		
		return $this->response;
	}
	
	public function update($table,$id_key,$id_val,$data){
	
		$this->response = [];
						
		$this->db->where("{$id_key} = {$id_val}");
		$this->db->update($table,$data);
		
				
	
		if($this->db->count){
			$this->response['success'] = true;
			$this->response['count'] = $this->db->count;
		}else{
			$this->response['success'] = false;
			$this->response['error'] = $db->getLastError();				
		}
		
		return $this->response;
	}
	
	public function delete($table,$where){
		$this->response = [];
		
		foreach($where as $k => $v){
			$this->db->where($k,$v);
		}
		$success = $this->db->delete($table);
		
				
		if($success){
			$this->response['success'] = true;
		}else{
			$this->response['error'] = $db->getLastError();				
		}
		
		return $this->response;		
	}
	
	
	/**
	* Gets the next available id from some table given the id column and assuming the id is an int.
	* @Param string $id name of column to get max on.
	* @Param string $table name of table to find max in.
	* @Return int
	*/	
	function getNextId($table,$col,$where=1){
		$this->db->where($where);
		$max = $this->db->getValue ($table, "max({$col})");
				
		return $max+1;		
	}
}

class ErrorHelp{
	function __construct($path="/var/www/html/bus/api/logs/error.log"){
		$this->path = $path;
	}
	
	function dump($error){
	
		file_put_contents($this->path,date("H:i:s D/M/Y",time())."\n",FILE_APPEND);
		file_put_contents($this->path,print_r($error,true),FILE_APPEND);
		file_put_contents($this->path,"\n",FILE_APPEND);
	}
}

