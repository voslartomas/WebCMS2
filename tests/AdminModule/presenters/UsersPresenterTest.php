<?php
    
class UsersPresenterTest extends \WebCMS\Tests\PresenterTestCase {
    
    protected $presenter = NULL;
    
    public function setUp(){
	parent::setUp();

	$this->presenter = $this->container
            ->getByType('Nette\Application\IPresenterFactory')
            ->createPresenter('Admin:Users');

        $this->presenter->autoCanonicalize = FALSE;
    }
    
    public function testPresenter() {
        
	$request = new Nette\Application\Request('admin:Users', 'GET', array());
        $response = $this->presenter->run($request);
	
	$template = $response->getSource();
	$template->registerHelperLoader('\WebCMS\Helpers\SystemHelper::loader');
	$template->setTranslator($this->presenter->translator);
	$template->settings = $this->presenter->settings;
	$template->render();
	
        $this->assertInstanceOf('Nette\Application\Responses\TextResponse', $response);
    }
    
    public function tearDown(){
	parent::tearDown();
    }
   
}