<?php
/**
 * Include mocks
 */
include_once 'Mocks/MiddlewareObject.php';
include_once 'Mocks/MiddlewareObject2.php';

class MiddlewareTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test creation of a middleware object
     * (which extends the Middleware class)
     */
    public function testObjectCreation()
    {
        $middleware = new MiddlewareObject();
        $this->assertTrue(is_object($middleware));
        $this->assertTrue($middleware instanceof Stackd\Middleware);
    }
    
    /**
     * Test invoking the call() method
     */
    public function testCallMethod()
    {
        $middleware = new MiddlewareObject();
        $this->assertEquals('Middleware 1', $middleware->call([], []));
    }
    
    /**
     * Test invoking the next() method
     */
    public function testNextMethod()
    {
        $middleware = new MiddlewareObject();
        $middleware2 = new MiddlewareObject2();
        
        $middleware->__inject($middleware2);
        
        $this->assertEquals('Middleware 2', $middleware->next([], []));
    }
    
    /**
     * Test invoking the next() method
     * when there isn't a next middleware
     * object available.
     * 
     * @expectedException Stackd\StackdException
     */
    public function testNextMethodException()
    {
        $middleware = new MiddlewareObject();
        $middleware->next([], []);
    }
    
}
