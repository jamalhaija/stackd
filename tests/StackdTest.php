<?php
/**
 * Include mocks
 */
include_once 'Mocks/MiddlewareObject.php';
include_once 'Mocks/NotMiddleware.php';

class StackdTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test creation of Stackd object
     */
    public function testObjectCreation()
    {
        $stack = new Stackd\Stackd([], []);
        $this->assertTrue(is_object($stack));
    }
    
    /**
     * Test creationg of Stackd object
     * with omission of request and response.
     * 
     * @expectedException Stackd\StackdException
     */
    public function testObjectCreationNoReqRes()
    {
        $stack = new Stackd\Stackd();
    }
    
    /**
     * Test running an empty stack
     * 
     * @expectedException Stackd\StackdException
     */
    public function testEmptyStack()
    {
        $stack = new Stackd\Stackd([], []);
        $stack->run();
    }
    
    /**
     * Create object and inject non Middleware object
     * 
     * @expectedException Stackd\StackdException
     */
    public function testNonMiddlewareObject()
    {
        $stack = new Stackd\Stackd([], []);
        $notMiddleware = new NotMiddleware();
        
        $stack->add($notMiddleware);
    }

    /**
     * TO DO : Test various scenarios of adding mixed types of middleware and running them.
     */
}
