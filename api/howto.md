## Add A Route

A route is the manner in which we communicate with the "backend", and by communicate we mean:
  - GET: Get some data
  - POST: Add new data
  - PUT: Update existing data
  - DELETE: Delete data
    
Examample api call:

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


## Add A Controller


## Add A Model
