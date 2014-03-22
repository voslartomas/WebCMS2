<?php
    
class UpdatePresenterTest extends \WebCMS\Tests\PresenterTestCase {
    
    public function setUp() {
	parent::setUp();
	
	$this->createPresenter('Admin:Update');
    }
    
    public function testDefault() {
        
        $response = $this->makeRequest();
	
        $this->assertInstanceOf('Nette\Application\Responses\TextResponse', $response);
	
	$this->getResponse($response);
    }
    
    public function testClearCache(){
	
	$response = $this->makeRequest('clearCache');
	
        $this->assertInstanceOf('Nette\Application\Responses\RedirectResponse', $response);
    }
    
    public function testAddModuleDialog(){
	
	$response = $this->makeRequest('addModule');
	
        $this->assertInstanceOf('Nette\Application\Responses\TextResponse', $response);
	
	$this->getResponse($response);
    }
    
    public function testFunctions(){
	
	$response = $this->makeRequest('functions');
	
        $this->assertInstanceOf('Nette\Application\Responses\TextResponse', $response);
	
	$this->getResponse($response);
    }
    
    public function testCreateModuleDialog(){
	
	$response = $this->makeRequest('createModule');
	
        $this->assertInstanceOf('Nette\Application\Responses\TextResponse', $response);
	
	$this->getResponse($response);
    }
}