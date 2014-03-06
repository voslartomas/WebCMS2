<?php

namespace WebCMS\Tests;

use \Mockery as m;
        
abstract class PresenterTestCase extends \PHPUnit_Framework_TestCase{
    
    protected $container;
    
    public function setUp() {
	parent::setUp();
	
	global $container;
        
        $this->container = $container;
    }
    
    public function tearDown() {
	parent::tearDown();
	
	m::close();
    }
}
    


