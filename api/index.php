<?php

// Holds all the packages we installed with composer
require './vendor/autoload.php';

// Have to set the timezone else php cries like a little bitch.
date_default_timezone_set("America/Chicago");


$configuration = [
    'settings' => [
        'displayErrorDetails' => true,
	    'determineRouteBeforeAppMiddleware' => true
    ],
];

$c = new \Slim\Container($configuration);
$app = new \Slim\App($c);

/****************************************************************************************************
* ROUTES
****************************************************************************************************/


$app->get('/','base');
$app->group('/v1', function () use ($app) {
	$app->get('/','v1base');
    $app->get('/users/', '\UserController:getUsers');
    $app->get('/users/{id}', '\UserController:getUser');
    $app->get('/menus/', '\MenuController:getMenus');
    $app->get('/menus/{id}', '\MenuController:getMenuItems');
    $app->get('/routes[/{id}]','MapController:getRoutes');
	$app->get('/bus_stops[/{id}]','MapController:getBusStops');
	$app->get('/gps_points/{type}/{id}','MapController:getGpsPoints');
    $app->post('/users/', '\UserController:addUser');
	$app->post('/logUser/','\UserController:logUser');
    $app->post('/menus/', '\MenuController:createMenu');
    $app->post('/menus/{id}', '\MenuController:addMenuItem');
    $app->put('/users/{id}', '\UserController:updateUser');
    $app->delete('/users/{id}', '\UserController:deleteUser');
    $app->delete('/menus/{menuId}[/{itemId}]', '\MenuController:deleteMenu');
	//$app->get('/[{path:.*}]', function($request, $response, $path = null) {
	//	return $response->write($path ? 'subroute' : 'index');
	//});
});


$app->run();


function base ($request, $response, $args) {
    
    v1base($request, $response, $args);
}

/**
* @Route: /user/
* @Description: Gets all users.
* @Example: curl -X GET https://msu2u.us/bus/api/v1/
*/
function v1base ($request, $response, $args) {
	global $app;

	$base_url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	
	$routes = $app->getContainer()->get('router')->getRoutes();
	$route_list = [];

	foreach($routes as $key =>$route){
		$method = $route->getMethods();
		$pattern = $route->getPattern();
		$pattern = substr($pattern,4);
		$route_list[$method[0]][] = $base_url.$pattern;

	}

	return $response->withStatus(200)
		->withHeader('Content-Type', 'application/json')
		->write(json_encode($route_list));
}




/****************************************************************************************************
* CONTROLLERS
****************************************************************************************************/

/**************************************************
* @Class: MapController
* @Description:
*		This controller interacts with the map model and directs all things maps.
* @Methods:
* 	getRoute() 		- GET gets info and points for a bus route
* 	getRoutes() 	- GET gets all routes
***************************************************/
class MapController{

	var $um;
	
	function __construct(){
		$this->mm = new MapModel();
	}
	
	/**
	* @Route: /route/
	* @Description: Gets all routes.
	* @Example: curl -X GET https://msu2u.us/bus/api/v1/routes/{id}
	*/
	public function getRoutes ($request, $response, $args) {

		if(isset($args['id'])){
			return $this->sendResponse($response,$this->mm->getRoutes($args['id']));
		}else{
			return $this->sendResponse($response,$this->mm->getRoutes());
		}

	}
	
	/**
	* @Route: /bus_stop/
	* @Description: Gets all or 1 bus stop.
	* @Example: curl -X GET https://msu2u.us/bus/api/v1/bus_stops/{id}
	*/
	public function getBusStops ($request, $response, $args) {

		if(isset($args['id'])){
			return $this->sendResponse($response,$this->mm->getBusStops($args['id']));
		}else{
			return $this->sendResponse($response,$this->mm->getBusStops());
		}

	}
	
	/**
	* @Route: /gps_points/
	* @Description: Gets gps points associated with a set of stops or a route.
	* @Example: curl -X GET https://msu2u.us/bus/api/v1/gps_points/{type}[/{id}]
	*/
	public function getGpsPoints ($request, $response, $args) {
		
		if(!isset($args['type']) || !isset($args['id'])){
			return json_encode(['success'=>false,'reason'=>'This route needs a \'type\' (route,bus_stop) and an \'id\'.']);
		}
		return $this->sendResponse($response,$this->mm->getGpsPoints($args));

	}
	
	
	/**
	* @Function: sendResponse
	* @Description: Packages up a response to send back to a request
	*/
	private function sendResponse($response,$results){
		return $response->withStatus(200)
			->withHeader('Content-Type', 'application/json')
			->write(json_encode($results));
	}
	
}


/**************************************************
* @Class: UserController
* @Description:
*		This controller interacts with the user model and directs all things user.
* @Methods:
* 	getUsers() 		- GET gets all users
*	getUser(int) 	- GET gets a single user based on id
*   addUser(json) 	- POST adds a user to the db
*   updateUser(json)- PUT updates an existing user
*   deleteUser(int) - DELETE deletes a user
***************************************************/
class UserController{

	var $um;
	
	function __construct(){
		$this->um = new UserModel();
	}

	/**
	* @Route: /user/
	* @Description: Gets all users.
	* @Example: curl -X GET https://msu2u.us/bus/api/v1/users/ 
	*/
	public function getUsers ($request, $response, $args) {

		return $this->sendResponse($response,$this->um->getUsers());

	}

	/**
	* @Route: /user/
	* @Description: Gets a single user.
	* @Example: curl -X GET https://msu2u.us/bus/api/v1/users/{id}
	*/
	public function getUser ($request, $response, $args) {

		return $this->sendResponse($response,$this->um->getUser($args['id']));

	}

	/**
	* @Route: /user/
	* @Description: Adds a single user.
	* @Example: curl -H "Content-Type: application/json" -X POST https://msu2u.us/bus/api/v1/users/ -d '{"fname": "Joe","lname": "Bob","user_type": "1","current_lat": "33.123","current_lon": "98.3434"}' 
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
	* @Example: curl -H "Content-Type: application/json" -X PUT https://msu2u.us/bus/api/v1/users/{id} -d '{"lname": "Cobby","user_type": "2"}' 
	*            curl -H "Content-Type: application/json" -X PUT https://msu2u.us/bus/api/v1/users/101 -d '{"lname": "Flabby","user_type": "1","current_lat": "33.88878"}'
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
	* @Example: curl -X DELETE https://msu2u.us/bus/api/v1/users/{id}
	*/
	public function deleteUser ($request, $response, $args) {

		$data = $request->getParsedBody();
			
		return $this->sendResponse($response,$this->um->deleteUser($args['id']));
				
	}
	
	
	/**
	* @Function: sendResponse
	* @Description: Packages up a response to send back to a request
	*/
	private function sendResponse($response,$results){
		return $response->withStatus(200)
			->withHeader('Content-Type', 'application/json')
			->write(json_encode($results));
	}
	
	/**
	* @Route: /logUser/{id}
	* @Description: Logs a user location.
	* @Example: curl -H "Content-Type: application/json" -X POST https://msu2u.us/bus/api/v1/logUser/ -d '{"user_id":"99","loc_data":{"speed": "55","altitude": "2000","current_lat": "33.123","current_lon": "98.3434"}}' 
	*/
	public function logUser ($request, $response, $args) {

		//Get the posted data from the request
		$data = $request->getParsedBody();

		
		//Add the user and send the response
		return $this->sendResponse($response,$this->um->postLocation($data));
		
	}
}


/**************************************************
* @Class: MenuController
* @Description:
*		This controller interacts with the menu model and does all things menu
* @Methods:
* 	getMenus() 			- GET gets all menus
*	getMenuItems(int) 	- GET gets a single menu based on id
*   createMenu(json) 	- POST adds a menu to the db
*   addMenuItem(json)	- PUT updates an existing menu
*   deleteMenu(int) 	- DELETE deletes a menu
*   deleteMenuItem(int,int) - needed
***************************************************/
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
	* @Example: curl -H "Content-Type: application/json" -X POST https://msu2u.us/bus/api/v1/menus/ -d '{"":""}'
	*/
	public function createMenu($request, $response, $args) {
	
		$data = $request->getParsedBody();
	
		return $this->sendResponse($response,$this->mm->createMenu($data));

	}

	/**
	* @Route: /menus/
	* @Description: Gets all menus.
	* @Example: curl -H "Content-Type: application/json" -X POST https://msu2u.us/bus/api/v1/menus/ -d '{"":""}'
	*/
	public function addMenuItem($request, $response, $args) {
	
		$data = $request->getParsedBody();
	
		return $this->sendResponse($response,$this->mm->addMenuItem($args['id'],$data));
	
	}

	/**
	* @Route: /user/
	* @Description: Deletes a single user.
	* @Example: curl -X DELETE https://msu2u.us/bus/api/v1/users/{id}
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
* MODELS
****************************************************************************************************/


/**
* This interfaces with the menus table.
* 
* @Method: array getRoutes()
*		
*/
class MapModel{
  /**
   * @var resource $db  database connection resource
   */
    var $db;
    var $response;        
    
	function __construct(){
		$this->db = new dbManager();
	}
	
	
  /**
   * Gets all the points in one route.
   * @Return array
   */	
	public function getRoutes($id=null){
	
		if($id){
			$temp1 = $this->db->fetch('select * from bus_routes where id = ?',array($id));
			$temp2 = $this->db->fetch('select * from bus_route_points where route_id = ?',array($id));
			$data['success'] = $temp1['success'] && $temp2['success'];
			foreach($temp1['data'][0] as $k => $v){
				$data['route_info'][$k] = $v;
			}
			$data['points'] = $temp2['data'];
			return $data;
		}else{
			return $this->db->fetch('select * from bus_routes');
		}
		
	}
	
  /**
   * Gets all the bus stops for a route or just all the bus stops.
   * @Return array
   */	
	public function getBusStops($id=null){
	
		if($id){
			$temp = $this->db->fetch('select * from bus_stops where route_id = ?',array($id));
		}else{
			$temp = $this->db->fetch('select * from bus_stops');
		}
		
		$data['success'] = $temp['success'];
		$data['points'] = $temp['data'];
		return $data;
		
	}
	
  /**
   * Gets all the gps points associated with a set of bus stops, or a route.
   * @Return array
   */	
	public function getGpsPoints($args){
	
		if($args['type'] == 'route'){
			$temp = $this->db->fetch('select * from bus_route_points where route_id = ?',array($args['id']));
		}else{//type==bus_stop
			$temp = $this->db->fetch('select * from bus_stops where route_id = ?',array($args['id']));
		}

		
		$data['success'] = $temp['success'];
		$data['points'] = $temp['data'];
		return $data;
		
	}
	
}

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
	
  /**
   * Adds a new location log to the system
   * @Param array data
   * @Return array
   */	
	public function postLocation($data){
		$temp = new ErrorHelp();
        $temp->dump($data);
        
        $time = $data['loc_data']['timestamp'];
        $loc = $data['loc_data']['coords'];
        $data = array('timestamp'=>$time);
        $data = array_merge($data,$loc);
        
		$temp->dump($data);
		return $this->db->insert('location_log',$data);
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



