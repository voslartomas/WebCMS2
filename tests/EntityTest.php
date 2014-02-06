<?php

namespace WebCMS\Tests;
    
require __DIR__ . '/bootstrap.php';
    
/**
 * @backupGlobals disabled
 */
abstract class EntityTestCase extends \PHPUnit_Framework_TestCase {
    
    protected $em;
    
    private function getClassesMetadata($path, $namespace){
	$metadata = array();
	
	if($handle = opendir($path)){
	    while(false !== ($file = readdir($handle))){
		if(strstr($file, '.php')){
		    list($class) = explode('.', $file);
		    if($class === 'Entity'){
			$metadata[] = $this->em->getClassMetadata($namespace . '\\Doctrine\\' . $class);
		    }else{
			$metadata[] = $this->em->getClassMetadata($namespace . '\\' . $class);
		    }
		}
	    }
	}
	
	return $metadata;
    }
    
    public function setUp() {
	parent::setUp();
	
	global $container;
	
	$this->em = $container->getService('doctrine.entityManager');
	
	$tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
	$tool->dropSchema($this->getClassesMetadata(__DIR__ . '/../entities', 'AdminModule'));
	$tool->createSchema($this->getClassesMetadata(__DIR__ . '/../entities', 'AdminModule'));
	
    }
    
    public function tearDown() {
	parent::tearDown();
	
	
    }
}
    