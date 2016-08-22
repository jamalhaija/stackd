<?php
/**
 * @package Stackd
 * @version 1
 */
 
namespace Stackd;

class Middleware
{
    /**
     * The next middleware to be invoked.
     */
    protected $next = null;
    
    /**
     * Inject the next middleware object into
     * self; to be invoked on the next() method.
     * 
     * @param Middleware $nextMiddleware The next middleware object
     */
    public function __inject(Middleware $nextMiddleware)
    {
        $this->next = $nextMiddleware;
    }
    
    /**
     * Invoke the next middleware object
     * 
     * @param mixed $request Representation of a request
     * @param mixed $reponse Representation of a response
     * @return mixed The retrun value of the next middleware object's call() method
     */
    public function next($request, $response)
    {
        if (is_null($this->next)) {
            throw new StackdException('There is no next middleware in the stack.');
        }
        
        return $this->next->call($request, $response);
    }
    
    /**
     * The call() method should be overridden by
     * the middleware implementation. By default this 
     * method simply calls next() and returns its return value.
     */
    public function call($request, $response)
    {
        return $this->next($request, $response);
    }
}
