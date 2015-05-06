# RockWall.PHP

Tiny (less than 30kb) and powerful JSON RESTful web-service engine for PHP :elephant:.


Basic useful feature list:

* Multiple requests (REST API) via single HTTP request.
* Unified and stable response format for all requests.
* Endpoint method execution by dependency of type detection (static or whether not).
* Request modifiers feature. Allows to declare which type of class instance should be used for each request (New instance, Singleton or new instance of Singleton).
* Fatal exceptions catching for every endpoint on low-level.
* Cross-domain AJAX requests support.
* Database PDO singleton instance available.
* One instance of engine for multiple different REST servers with different configurations.
* Extra simple in use and well structured in filesystem.
* Client side sandbox available for testing.

## Quick Start Example

### Client side

```javascript
var url = '//your.server.url';
var request = {
    "request":
        [
          { "cmd":"namespace/class/method" , "args":{"Awesome":"Hello World!"} },
          { "cmd":"namespace/class/method" , "args":{"makeError":true} },
          { "cmd":"not/existing/endpoint" },
          { "cmd":"error-command"         },
          { "cmd":"namespace/class/method"}
        ]
};

$.ajax(url, {
    type: 'POST',
    data: request,
    success: function (response) {
        // See "Server response"
        console.log(response);
    }
});
```

### Server side

```php
<?php // File: {server}/namespace/class.php

class svc_namespace_class {

    private $increment = 0;

    public
    function method (array $args, $cmd) {
        $this->increment++;

        if (isset($args["makeError"])) {
            return new response(null, "Awesome error", 12345);
        }

        $data = $args;
        $data["increment"] = $this->increment;
        return new response($data);
    }

}

```

### Server response
```javascript
// response variable in success callback
{
    "error"  : "Awesome error",
    "code"   : 12345,
    "errors" :
        {
            // The key is the same as index in the data array
            "1" : { "error":"Awesome error" , "code":12345 } ,
            "2" : { "error":"Not found" , "code":404 } ,
            "3" : { "error":"Invalid request"  , "code":400 } ,
        },
    "data"   :
        [
         /*0*/ { "data" : {"increment":1 , "Awesome":"Hello World!"} } ,
         /*1*/ { "error":"Awesome error" , "code":12345 },
         /*2*/ { "error":"Not found" , "code":404 } ,
         /*3*/ { "error":"Invalid request"  , "code":400 } ,
         /*4*/ { "data" : {"increment":3} }
        ]
}
```

> Note:
> In the root directory of engine you can find
> "[sandbox.html](sandbox.html "Download the file and put it where you need")"
> file, that can be useful for testing and development of your server from client.
> 
> [Online version](https://rawgit.com/rockwall/RockWall.PHP/master/sandbox.html "Visit online version of RockWall REST Server Sandbox") (For remote testing only)

## Client API

This engine allows quick and simple to create data channel between client and
server and provides opportunity to execute multiple REST requests via single
HTTP request that increases performance of client through decreasing amount of
HTTP requests.

To working with any endpoint of server and generally with server, used unified
system of Request and Response objects.

### Request object

Request object allows to describe which endpoints of server should be called,
an order of execution, environment of execution for every endpoint and which
arguments should to be passed for every endpoint call.

#### Request object structure

Request object should to be passed to the server as `request` variable that is
an array of objects.

Every object of array describes the call to the different endpoints of the
server and should contains the `cmd` (command) property and `args` property as
an optional.


```javascript
{
  "request":
    [
      { "cmd":String , "args":Object },
      ...
    ]
}
```

The command is a slash separated string that describes a path to the endpoint
on the server and should consists of `namespace`, `class` and `method`.
The `class` can be not defined for the case when is the main class of
`namespace` (has a name that same as namespace name).

```javascript
"cmd": "namespace/class/method"
```
or
```javascript
"cmd": "namespace/method"
```

Also the command can contain modifier that defines which environment should be
used for each call.

#### Modifiers

The method can be called at the new instance of class or Singleton instance
that life cycle limited by life cycle of HTTP request.
The modifier is a command prefix that can be one of the follow:

| Modifier | Description                                                                   | Example                 |
|:--------:|:------------------------------------------------------------------------------|:------------------------|
|    @     | Creates the new instance of class for method execution                        | @namespace/class/method |
|    &     | `Default` Uses Singleton instance of class or creates if it in not exists yet | &namespace/class/method |
|    !     | Replaces old Singleton instance by new instance of class and uses it          | !namespace/class/method |


**Example for JavaScript AJAX request by using jQuery**

```javascript
var url = '//your.server.url';
var request = {
    "request":
        [
          { "cmd":"namespace/class/method" , "args":{"hello":"world"} },
          { "cmd":"namespace/method"      },
          { "cmd":"&namespace/method"     },
          { "cmd":"!namespace/method"     },
          { "cmd":"@namespace/method"     },
          { "cmd":"not/existing/endpoint" },
          { "cmd":"error-command"         }
        ]
};

$.ajax(url, {
    type: 'POST',
    data: request,
    success: function (response) {
        // Process response object here
        console.log(response);
    }
});
```

### Response object

The engine provides unified response object structure for working with every
endpoint of server and generally with server that allows to simplify for using
and understanding it for even for junior developer.

**Response object instance can be of two types:**

`Single` : Simple object with `data` property when successful whether `error`
           and `code` properties when error occurred.

**Result is successful**
```javascript
{
    "data" : Mixed
}
```

**When error occured**
```javascript
{
    "error" : String,
    "code"  : Number
}
```

`Multiple` : is a collection of `simple` responses where `error` and `code`
             properties is an error of FIRST simple response that returns error
             and located in `data` array.

The list of all errors of `multiple` response is available in `errors` object.

`errors` - is an object of objects. Each child of `errors` is object that named
           as his `index` in `data` array.

**Multiple response structure**

```javascript
{
    // [Optional] An error message of FIRST simple response of the queue that returns error
    "error"  : String,
    // [Optional] An error code of FIRST simple response of the queue that returns error
    "code"   : Number,
    // [Optional] The list of all errors of the queue
    "errors" :
        {
            "(index in data array)" : { "error":String , "code":Number } ,
            // ...
        },
    "data"   :
        [
            { "data" :Mixed },
            { "error":String , "code":Number } ,
            // ...
        ]
}
```

**For example**

The client sent request to the server and server returns errors for the second
and third commands.

Request object:

```javascript
{
    "request":
        [
          { "cmd":"namespace/class/method" },
          { "cmd":"not/existing/endpoint"  },
          { "cmd":"error-command"          },
          { "cmd":"namespace/method"       }
        ]
}
```

Response object:

```javascript
{
    "error"  : "An error occurred (second)",
    "code"   : 12345,
    "errors" :
        {
            // The key is the same as index in the data array
            "1" : { "error":"An error occurred (second)" , "code":12345 } ,
            "2" : { "error":"An error occurred (third)"  , "code":67890 } ,
        },
    "data"   :
        [
         /*0*/ { "data" : {/*Some data*/} } ,
         /*1*/ { "error":"An error occurred (second)" , "code":12345 } ,
         /*2*/ { "error":"An error occurred (third)"  , "code":67890 } ,
         /*3*/ { "data" : {/*Some data*/} }
        ]
}
```

## Server API

> The engine is written it so that allows to reuse one instance for multiple
> different REST servers with different configurations. Has no sense to
> duplicate engine instances for every REST server. Practically, will be better
> put one instance of engine beyond the pale of document root and include it
> where it need.

### Installation

Download the engine as ZIP archive from
[[here](https://github.com/rockwall/RockWall.PHP/archive/master.zip "ZIP archive")]
and unpack it OR clone GIT project of engine from
[[here](https://github.com/rockwall/RockWall.PHP.git "GIT repository")].

Put the instance of engine beyond the pale of document root.

### Integration

Create your REST server that should include the main endpoint file `index.php`,
config file `config.php` and the folder `svc` where you want to deploy your services.

> Any part of your REST-server is independent and can be renamed or moved where you want.
> The only thing that you have to do is to define path to the parent folder of your services
> in configuration file and include to endpoint file your configuration file.

The `config.php` file:

The configuration file should implement the `config` class with structure same as shown below

```php
<?php

class config {

    public static
    function db () { //optional
        $o = new stdClass;
        $o->host = 'Your DB Host';
        $o->user = 'Your DB User';
        $o->pasw = 'Your DB Password';
        $o->name = 'Your DB Name';
        return $o;
    }

    public static
    function dir () {
        $o = new stdClass;

        // Enter your path to the directory where located your services
        $o->svc = __DIR__;

        return $o;
    }

    public static
    function main () {
        $o = new stdClass;

        // The classname prefix. Format: {prefix}_{namespace}[_{class}]
        $o->prefix = 'svc';

        // @ : The new instance | & : Singleton | ! : Rewrite Singleton instance
        $o->defaultModifier = '&';

        // Use default modifier for all or not
        $o->strictModifier = false;

        return $o;
    }

}
```

The `index.php` file:

```php
<?php

// Include the `.inc.php` file that located in the main directory of engine
require_once('/engine/.inc.php');

// Include your custom configuration file
require_once('/servers/provider.X/config.php');

// Start the engine initialization process
svc::init();
```

In practice, the most optimal and safe structure in file system is presented below:

```
[engine]
  | .inc.php

[servers]
  | [provider.1]
  | [provider.2]
  | ...
  | [provider.X]
      | config.php
      | [namespace1]
      | [namespace2]
      | ...
      | [namespaceX]
          | class1.php
          | class2.php
          | ...
          | classX.php

[www]
  | [document root of provider.1]
  | [document root of provider.2]
  | ...
  | [document root of provider.X]
      | index.php
```

### Services implementation

**Agreements of implementation:**

* Each endpoint is a public method of class.
* Each class should be implemented into the own file.
* The name of file should to be same as logical name of class.
* The name of class should contain an information about the namespace,
  own logic name and the prefix that is defined by configuration that
  required to avoid name collisions.
* The name of main class (if exists) of namespace should contain all
  foregoing information, except of logical name of class that is same
  as name of namespace.
* Any endpoints can be logically combined as methods of mutual class
  and/or by mutual folder of classes (aka. namespace).
* Any method can be called at the new instance of class or Singleton instance
  that life cycle limited by life cycle of HTTP request and defined by client
  (see [Modifiers](#modifiers)) or configuration
  (see [defaultModifier, strictModifier](#integration)).
* Each endpoint should return an instance of `response` class that
  allows to declare besides of response data also error of execution.

Based on the foregoing we have something like this:

For the client side request
```javascript
{
    "request":
        [
          { "cmd":"namespace/class/method" , "args":{"hello":"world"} },
          { "cmd":"namespace/method"       , "args":{"hello":"world"} }
        ]
}
```
On the server side should be implemented next

`namespace/class.php`:
```php
<?php

class svc_namespace_class {

    public function method (array $args, $cmd) {
        $data = null;
        return new response($data);
    }

}

```

`namespace/namespace.php`:
```php
<?php

class svc_namespace {

    public function method (array $args, $cmd) {
        $data = null;
        return new response($data);
    }

}

```

### Extra features

In addition the engine provides Singleton instance of
[PDO](http://php.net/manual/en/book.pdo.php "PHP Data Object") connector for MySQL.
This feature is optional.

Connection credentials can be defined by [configuration](#integration).

The function `db()` is available from any environment and returns the instance of connector.

Usage example:
```php
$date = db()->query('SELECT NOW() AS date')->fetchObject()->date;
```
> The complete documentation for PDO (PHP Data Objects) can be found
> [here](http://php.net/manual/en/book.pdo.php "PHP Data Objects").
