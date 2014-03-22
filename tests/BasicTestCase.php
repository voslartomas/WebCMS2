<?php

namespace WebCMS\Tests;

use \Mockery as m;
        
abstract class BasicTestCase extends \PHPUnit_Framework_TestCase {
    
    protected $container;
    
    protected $em;
    
    public function setUp() {
	parent::setUp();
	
	global $container;
	
	$this->container = $container;
	
	$this->em = $container->getService('doctrine.entityManager');
    }
    
    public function tearDown() {
	parent::tearDown();
	
	m::close();
    }
}