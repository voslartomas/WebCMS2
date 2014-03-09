<?php
    
class UsersPresenterTest extends \WebCMS\Tests\PresenterTestCase {
    
    public function setUp() {
	parent::setUp();
	
	$this->createPresenter('Admin:Users');
    }
    
    public function testDefault() {
        
        $response = $this->makeRequest();
	
        $this->assertInstanceOf('Nette\Application\Responses\TextResponse', $response);
	
	$this->getResponse($response);
    }
    
    public function testUpdateUserDialog(){
	
	$response = $this->makeRequest('updateUser');
	
        $this->assertInstanceOf('Nette\Application\Responses\TextResponse', $response);
	
	$this->getResponse($response);
    }
    
    public function testRoles(){
	
	$response = $this->makeRequest('roles');
	
        $this->assertInstanceOf('Nette\Application\Responses\TextResponse', $response);
	
	$this->getResponse($response);
    }
    
    public function testUpdateRoleDialog(){
	
	$response = $this->makeRequest('updateRole');
	
        $this->assertInstanceOf('Nette\Application\Responses\TextResponse', $response);
	
	$this->getResponse($response);
    }
}