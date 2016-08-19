# Stackd
Simple middleware for PHP.

* Lightweight
* Simple API
* Convention over configuration
* Injection of dependencies shared across all middleware components

## How to use
Stackd is comprised of two main concepts:
* A controller (a Stackd object) which builds and runs the stack of middleware components
* Middleware objects or functions which represent the various components of our application

### Simple example
```php
//Create new middleware controller
$onion = new Stackd;

//Add middlewares
$onion->add(new MiddlewareClass);
$onion->add(function() {...});
$onion->add(ClassName::class);

//Run down and then up the stack
$onion->run();
```
The order in which we `add()` our middleware components will be the order in which they will be executed down the stack, followed by reverse order after reaching the last component.

So, a stack of ABCD will be executed in order and then bubble back up : ABCDCBA.

### The Middleware object
Middleware components must extend the `Middleware` class, which has a couple conventional methods.

The `call()` method is the "main" method which is invoked when a component is reached in the stack.

The `next()` method invokes the next component's `call()` method. The `next()` method is required to continue down the stack.

In an example:

```php
class MyMiddleware extends Middleware
{
   public function call()
   {
       //Do something before calling the next middleware component
       $this->someBeforeMethod();
       
       //Call the next middleware component
       $this->next();
       
       //Do something after calling the next middleware component 
       $this->someAfterMethod();
   }
}
```

### Anonymous function
A middleware component can also be an anonymous function and may use the Stackd object's `next()` method:

```php
$onion = new Stackd;

$onion->add(function() use ($onion) {
    //Do something before
    
    //Call next middleware component
    $onion->next();
    
    //Do something after
});
```

### Class name
Middleware components can also be added as a class name. In this case, the object will be instantiated at the very last moment: when `next()` is called by the previous middleware component.

We must ensure to use `::class` when adding class names to our stack:

```php
$onion = new Stackd;

$onion->add(ClassName::class);
$onion->add(AnotherClass::class);

$onion->run();
```

### Stopping execution
If a `return` statement is made before calling the `next()` method in a middleware component, execution ends, and the stack will begin bubbling back up.

For example, if we have a stack of components ABCDE, and component D has a `return` statement in this scenario, then component E will not be reached, and the execution order will be ABCDCBA:

```php
class D extends Middleware
{
    public function call()
    {
        if (!$this->doSomeValidation()) {
            return false;
        }
        
        //Never reached if above condition evaluated to true
        $this->next();
    }
}
```

If the `next()` method is not called from within a middleware component, then execution also ends as we never proceed to the next component.

## Dependency injection
Dependencies can be injected via the `inject()` method.

```php
$onion = new Stackd;

$onion->inject('myService', new SomeService);
$onion->inject('myDependency', $someDependency);

...
```

Dependencies can then be accessed from within the middleware component:

```php
class MyMiddleware extends Middleware
{
    public function call()
    {
        //Call a dependency
        $this->myService->doSomething();
        
        $this->next();
    }
}
```

Or via the `Stackd` instance itself:

```php
$onion->add(function() use ($onion) {
    $onion->myService->doSomething();
    
    $onion->next();
});
```

There are no restrictions to what can be injected into the container. Stackd essentially acts as a parameter bag.

```php
$onion->inject('someService', new ClassObject);
$onion->inject('someDependency', ClassName::class);
$onion->inject('functionDependency', function() {...});
$onion->inject('myConfig', ['param' => $value]);
$onion->inject('literalValue', 3.14);
```

### Injection by reference
All objects and variables are injected into the middleware component by reference.

If we want a local object to be created within our class, for example, we should pass the class name instead of an instantiated object.

```php
//ServiceClass has a member with value of false
$onion->inject('myObject', new ServiceClass);
$onion->inject('myClass', ServiceClass::class);

$onion->add(function() use ($onion) {
    //Set member value on myObject
    $onion->myObject->member = true;
    
    //Create a new object
    $newObj = new $onion->myClass;
    $newObj->member = true;
    
    $onion->next();
});

$onion->add(function() use ($onion) {
    echo $onion->myObject->member; //true; set in previous middleware
    
    $newObj = new $onion->myClass;
    echo $newObj->member; //false; local scope object
});
```

Also, we can keep in mind that anonymous functions can be used to create new instances acting as a factory-type construct:

```php
$onion->inject('myFactory', function() {
    return new SomeClass;
});

$onion->add(function() use ($onion) {
    $obj = $onion->myFactory(); //New instance of SomeClass
});

...
```

### Lifecycle
When `call()` is invoked, the following takes place in order:

1. The `Middleware` class is instantiated and `__construct()` is called.
2. Dependencies are injected into the middleware component via built-in `__inject()` method.
3. The middleware object's `call()` method is invoked

This means that dependencies are not available in `__construct()`, as they are injected after the fact.

This is not the case if the middleware component is defined as an anonymous function, as there is no object instantiation or invocation of the `call()` method. Dependencies are available immediately through the `Stackd` instance.

### Layer-specific dependencies
The entire purpose of Stackd's dependency container is to share dependencies across all layers of the application.

To inject layer-specific dependencies only into the middleware components that need them, we can do so through the component's constructor.

```php
$onion = new Stackd();

$onion->inject('sharedService', new SharedService);

$onion->add(new MyMiddleware(new SpecificDependency));

$onion->run();
```

In the middleware component, we can handle it as:

```php
class MyMiddleware extends Middleware
{
    private $mySpecificDependency;
    
    public function __construct(SpecificDependency $sd)
    {
        $this->mySpecificDependency = $sd;    
    }
    
    public function call()
    {
        $this->sharedService->doSomething(); //Available automatically
        $this->mySpecificDependency->doSomething(); //Only available to this component
        
        $this->next();
    }
}
```

## Bringing it all together
Stackd tries to remain very thin, allowing you to use any combination of 3rd party libraries (or your own components) to do the specific tasks that you need.

Stackd is really a pattern more than a framework or library, trying to solve a specific problem. We can pick and choose the libraries and components we need and discard those we do not instead of getting married to a framework.

Here's an example of how we may use this pattern to rapidly build the foundation to our application:

```php
include 'autoloader.php';

//3rd party dependencies
$request = new ThirdParty\Http\Request;
$response = new ThirdParty\Http\Response;
$router = new ThirdParty\Router\Router;
$onion = new Stackd\Stackd();

//My application components
$auth = new MyApp\Authenticator;
$valid = new MyApp\Validator;

//Inject dependencies shared across entire app
$onion->inject('request', $request);
$onion->inject('response', $response);

//Add middleware layers, each has a call() and next() method
$onion->add($auth);
$onion->add($valid);

//Make use of a 3rd party routing component;
//We're using it only once, so no need to share it across entire middleware stack
$onion->add(function() use ($onion, $router) {
    $router->get(...);
    $router->post(...);

    ...
});

//Run the stack
$onion->run();
```

## API Reference

### Stackd Class
The is the main middleware stack runner. Object instantiated with `new Stackd`.

| Method                                     | Description
|--------------------------------------------|-------------
| add (_mixed middleware_)                   | Add _middleware_ to the middleware stack. _middleware_ can be an object, function, or class name denoted with ::class.
| inject (_string name_, _mixed dependency_) | Inject a _dependency_ to be shared across all layers of the stack (available to every middleware component).
| next (_void_)                              | Invoke the call() method of the next middleware component. Useful to invoke the next middleware from an anonymous function.
| run (_void_)                               | Run through the middleware stack from top to bottom, then again, bottom to top.

### Middleware Class
If the middleware component is a class, it should extend the `Middleware` class.

The middleware component class must have a `call()` method to be invoked by the previous middleware component.

| Method        | Description
|---------------|-------------
| next (_void_) | Invoke the next middleware component