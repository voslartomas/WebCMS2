<?php
    
class FilesystemPresenterTest extends \WebCMS\Tests\PresenterTestCase {
    
    public function setUp() {
	parent::setUp();
	
	$this->createPresenter('Admin:Filesystem');
    }
    
    public function testDefault() {
        
	$response = $this->makeRequest();
	
        $this->assertInstanceOf('Nette\Application\Responses\TextResponse', $response);
	
	$this->getResponse($response);
    }
    
    public function testCreateDirectoryDialog(){
	
	$response = $this->makeRequest('default', 'GET', array(
	    'action' => 'default',
            'do' => 'makeDirectory',
	));
	
	$this->assertInstanceOf('Nette\Application\Responses\TextResponse', $response);
    }
    
    public function testMakeDirectory(){
	
	$response = $this->makeRequest('default', 'GET', array(
	    'action' => 'default',
            'do' => 'makeDirectory',
	    'name' => 'test directory'
	));
	
	$this->assertInstanceOf('Nette\Application\Responses\TextResponse', $response);
	$this->assertEquals(TRUE, file_exists('upload/test-directory/'));
    }
    
    public function testRemove(){
	
	mkdir('upload/test-directory');
	mkdir('thumbnails/test-directory');
	
	$this->assertEquals(TRUE, file_exists('upload/test-directory/'));
	$this->assertEquals(TRUE, file_exists('thumbnails/test-directory/'));
	
	$response = $this->makeRequest('default', 'GET', array(
	    'action' => 'default',
            'do' => 'deleteFile',
	    'pathToRemove' => 'upload/test-directory'
	));
	
	$this->assertInstanceOf('Nette\Application\Responses\RedirectResponse', $response);
	$this->assertEquals(FALSE, file_exists('upload/test-directory/'));
	$this->assertEquals(FALSE, file_exists('thumbnails/test-directory/'));
    }
    
    public function testDownloadFile(){
	
	file_put_contents('upload/test.txt', 'Test text.');
	
	$response = $this->makeRequest('downloadFile', 'GET', array(
	    'action' => 'downloadFile',
	    'path' => 'test.txt'
	));
	
	$this->assertInstanceOf('Nette\Application\Responses\FileResponse', $response);
    }
}