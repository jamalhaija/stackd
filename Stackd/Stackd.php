<?php
/**
 * @package Stackd
 * @version 1
 */
 
namespace Stackd;

class Stackd
{
    /**
     * The stack of middlewares
     */
    private $stack = [];
    /**
     * Request object
     */
    private $request;
    /**
     * Response object
     */
    private $response;
    
    /**
     * Create Stackd object with request/response
     * 
     * param mixed $request Represenation of a request
     * param mixed $reponse Representation fo a response
     */
    public function __construct($request = null, $response = null)
    {
        if (is_null($request) OR is_null($response)) {
            throw new StackdException('A request and a response are required in the constructor.');
        }
        $this->request = $request;
        $this->response = $response;
    }
    
    /**
     * Add a middleware object, class or function
     * 
     * param mixed $middleware A middleware; must be an object or class of type Stackd\Middleware or a callable.
     */
    public function add($middleware)
    {
        if (is_string($middleware)) {
            $middleware = new $middleware();
        }
        
        if (!$middleware instanceof Middleware) {
            throw new StackdException('Middleware must be instance of Middleware.');
        }
        
        if (isset($this->stack[0])) {
            $this->stack[0]->__inject($middleware);
        }
        
        array_unshift($this->stack, $middleware);
    }
    
    /**
     * Run the stack.
     */
    public function run()
    {
        if (empty($this->stack)) {
            throw new StackdException('Tried to run an empty middleware stack.');
        }
        
        $invoker = end($this->stack);
        
        return $invoker->call($this->request, $this->response);
    }
}
