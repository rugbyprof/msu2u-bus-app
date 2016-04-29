## Routes

Documentation for Slim3 routes: http://www.slimframework.com/docs/objects/router.html

A route is the manner in which we communicate with the "backend", and by communicate we mean:
  - GET: Get some data
  - POST: Add new data
  - PUT: Update existing data
  - DELETE: Delete data
    
Example api call:

```
https://msu2u.us/bus/api/v1/user/3
```

Where:
- URL = `https://msu2u.us/bus/api/v1/`
- ROUTE = `user`
- PARAMS = `/3`

And would return:

```json
{
"success": true,
"data": [
    {
        "id": 3,
        "fname": "Susan",
        "lname": "Jones",
        "user_type": 1,
        "current_lat": -98.9999999,
        "current_lon": 33.999999,
        "timestamp": 0,
        "on_bus": 0
    }
  ]
}
```

The actual code for the route would look like this:

```php
    $app->put('/user/{id}', '\UserController:updateUser');
```

Where: 
- `'/user/{id}'`
    - This defines the route where `/user/` identifies which method to run and `{id}` is a parameter to send to the method
- `'\UserController:updateUser'`
    - This points to a class and a method.
    - UserController = a class name
    - updateUser = a method in the above class


## Controllers

Here is a part of the `UserController` that the above route uses:

```php
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
	
```

First of all a controller is meant as a means of "connecting" two or more things. Typically a route has a controller in order to attach functionality to the route. The controller can be used in may ways, it's typically used to get data (possibly from multiple sources), format the data,  pass the data on to something. 

In order to get data we need a data source. In our case, were using mysql. That's why this line is in the constructor:
```php
$this->um = new UserModel();
```
It creates an instance of a model (which connects to our database), thereby "connecting" us to the data. The route determines what we do with the data (GET, PUT, POST, DELET). And the parameters (args) determines which rows in the database get manipulated. 

Each method receives three parameters: `$request`, `$response`, and `$args`. I won't get into each of the params right now, but 
I will mention the `$args` param. This is an [associative array](http://php.net/manual/en/language.types.array.php) that contains each of the parameters passed in from the route. 

The route: `https://msu2u.us/bus/api/v1/user/{id}` calls the appropriate class method and makes the user id available like so: `$args['id']`. It uses the `id` in the `$args` array to make this call:
```php
return $this->sendResponse($response,$this->um->getUser($args['id']));
```

So lets break this line down:
```php
return 
    //build a response object
    $this->sendResponse(
    	//using the existing reponse object passed in
        $response,
        //Call the user model and get the user data based on the id
        $this->um->getUser($args['id'])
    );

//This is just a standardized response method to package retreived data or an appropriated message in json
//and send it back to the requester. We could make this way more robust, but keeping it simple right now.
private function sendResponse($response,$results){
	return $response->withStatus(200)				//Status 200 means everythings ok
		->withHeader('Content-Type', 'application/json')	//add a json header to "type" the return data
		->write(json_encode($results));				//add our response data
}
```



## Models

I'll discuss models this weekend, and add an example on retreiving a bus route.
