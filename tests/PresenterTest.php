<?php

namespace WebCMS\Tests;

use \Mockery as m;
        
abstract class PresenterTestCase extends \PHPUnit_Framework_TestCase{
    
    public function setUp() {
	parent::setUp();
	
	global $container;
    }
    
    public function tearDown() {
	parent::tearDown();
	
	m::close();
    }
}
    


