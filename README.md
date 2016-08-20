# Stackd
Simple middleware for PHP.

Stackd does one thing and one thing only: it passes a request and a response down and then back up a stack of middleware components.

Stackd is really a pattern more than a framework or a library. It tries to remain very thin, providing a foundation to rapidly build "layered" applications where each middleware compnent implements a distinct behavior.

## Basic Usage
```php
$onion = new Stackd(new Request, new Response);

$onion->add(new Authentication);
$onion->add(new Authorization);
$onion->add(new ContentNegotiation);
$onion->add(new Dispatcher);

$onion->run();
```

The order in which we `add()` our middleware components will be the order in which they will be invoked down the stack, and then bubble up the stack in reverse order after reaching the last component.

```
Authentication
Authorization
ContentNegotiation
Dispatcher
ContentNegotiation
Authorization
Authentication
```

## Middleware Logic
The middleware components themselves can be:

* Objects
* Class names
* Anonymous functions

### Middleware : Objects
Middleware objects must be of a class that extends Stackd's `Middleware` class, which provides a `call()` method that will be invoked when the middleware component is reached in the stack.

The `call()` method should be overridden to implement your component's logic.

```php
class MyMiddlware extends Middleware
{
    //This method will be invoked automatically
    //when this middleware is reached.
    public function call($request, $response)
    {
        //Do something before the next middleware
        $this->doSomething();
        
        //Call the next middleware component in the stack
        $response = $this->next($request, $response);
        
        //Do something after
        $this->doSomethingElse();
    }
}
```

As in the above example, the next middleware is invoked by calling the `next()` method. The parameters provided to the `next()` method will be passed to the next middleware's `call()` method.

### Middleware : Class Names
Class names can be provided as middleware components instead of objects.

The only difference is that instantiation of the object will take place just in time for it to be invoked, instead of during the initialization of the middleware stack.

Once the object is instantiated, then it will be handled in exactly the same way as a middleware object would (as described in the above section).

When adding class names, you must ensure to use the `::class` notation:

```php
$onion = new Stackd(new Request, new Response);

$onion->add(MyClass::class);
$onion->add(AnotherClass::class);

$onion->run();
```

### Middleware : Anonymous Functions
Middleware components can also be anonymous functions (or any callables). When a function is provided, that function acts as the `call()` method.

The `next()` method can be invoked by using the Stackd object:

```php
$onion = new Stackd(new Request, new Response);

$onion->add(function($request, $response) use ($onion) {
    //Do something before
    
    //Call the next middleware component
    $response = $onion->next($request, $response);
    
    //So something after
});
```

### The next() method
The responsibility of invoking the next middleware component is delegated to the middleware components themselves. This means if the `next()` method is not called, then traversing down the stack will not continue.

Stackd kicks off the stack with the first middleware, but leaves it up to the implementation of the individual middleware components to handle the request/response and the passing of these parameters.


### Middleware lifecycle
When instantiating objects, it's important to note that the next middleware component is not available to the constructor.

The following takes place in order:

1. Object is instantiated; `__construct()` is invoked.
2. Next middleware component is injected into the object via the built-in `__inject()` method.
3. The `call()` method is invoked and the request/response are passed down as parameters.

## The Request & Response

Stackd does not care what the request and response actually are. They can be objects, hashes, structs or any constructs that represent the request/response.

Validation and interface type checking is delegated to the middleware logic.

```php
class MyMiddleware extends Middleware
{
    public function call(Psr\Http\Message\RequestInterface $request, Psr\Http\Message\ResponseInterface $response)
    {
        ...
    }
}
```

## API Reference

### Stackd Class
The is the main middleware stack runner.

| Method                                         | Description
|------------------------------------------------|-------------
| __contruct (_mixed request_, _mixed response_) | Instantiate a Stackd object and pass a representation of a request and a response.
| add (_mixed middleware_)                       | Add _middleware_ to the middleware stack. _middleware_ can be an object, function, or class name denoted with ::class.
| next (_mixed request_, _mixed response_)       | Invoke the call() method of the next middleware component. Useful to invoke the next middleware from an anonymous function.
| run (_void_)                                   | Run through the middleware stack from top to bottom, then again, bottom to top.

### Middleware Class
If the middleware component is a class, it should extend the `Middleware` class.

The middleware component class must have a `call()` method to be invoked by the previous middleware component.

| Method                                   | Description
|------------------------------------------|-------------
| next (_mixed request_, _mixed response_) | Invoke the next middleware component
