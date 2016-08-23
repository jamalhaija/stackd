<?php
/**
 * Include mocks
 */
include_once 'Mocks/MiddlewareObject.php';
include_once 'Mocks/NotMiddleware.php';
include_once 'Mocks/MWA.php';
include_once 'Mocks/MWB.php';
include_once 'Mocks/MWC.php';

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
     * Build stack using middleware objects
     */
    public function testObjectStack()
    {
        $stack = new Stackd\Stackd('', '');
        
        $stack->add(new MWA);
        $stack->add(new MWB);
        $stack->add(new MWC);
        
        $this->assertEquals('ABCBA', $stack->run());
    }
    
    /**
     * Build stack using class names
     */
    public function testClassStack()
    {
        $stack = new Stackd\Stackd('', '');
        
        $stack->add(MWA::class);
        $stack->add(MWB::class);
        $stack->add(MWC::class);
        
        $this->assertEquals('ABCBA', $stack->run());
    }
    
    /**
     * Build stack using a combination of objects
     * and string class names
     */
    public function testMixedStack()
    {
        $stack = new Stackd\Stackd('', '');
        
        $stack->add(MWA::class);
        $stack->add(new MWB);
        $stack->add(MWC::class);
        
        $this->assertEquals('ABCBA', $stack->run());
    }

}
