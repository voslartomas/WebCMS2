<?php

use \Mockery as m;

class ModuleTest extends \WebCMS\Tests\BasicTestCase {
	
    public function testModule(){
	
	$moduleMock = $this->getMockForAbstractClass('WebCMS\Module');
	
	$moduleMock->setName('Test module');
	$moduleMock->setAuthor('Name Surname');
	$moduleMock->setPresenters(array(
			    array(
				    'name' => 'Module',
				    'frontend' => TRUE,
				    'parameters' => FALSE
				    ),
			    array(
				    'name' => 'Settings',
				    'frontend' => FALSE
				    )
			    ));
	
	$moduleMock->addBox('name', 'Module', 'function', 'ModuleName');
	
	$presenter = $moduleMock->getPresenterSettings('Module');
	
	$this->assertEquals('Test module', $moduleMock->getName());
	$this->assertEquals('Name Surname', $moduleMock->getAuthor());
	$this->assertCount(2, $moduleMock->getPresenters());
	$this->assertEquals(array(
				    array(
					    'name' => 'Module',
					    'frontend' => TRUE,
					    'parameters' => FALSE
					    ),
				    array(
					    'name' => 'Settings',
					    'frontend' => FALSE
					    )
			    ), $moduleMock->getPresenters());
	$this->assertEquals('Module', $presenter['name']);
	$this->assertTrue($presenter['frontend']);
	$this->assertFalse($presenter['parameters']);
	$this->assertEquals(array(array(
	    'key' => 'name',
	    'name' => 'name',
	    'presenter' => 'Module',
	    'module' => 'ModuleName',
	    'function' => 'function'
	)), $moduleMock->getBoxes());
	$this->assertFalse($moduleMock->isSearchable());
	$this->assertFalse($moduleMock->isTranslatable());
	$this->assertFalse($moduleMock->isSearchable());
    }
}