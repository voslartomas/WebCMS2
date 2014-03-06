<?php

class UsersPresenterTest extends \PHPUnit_Framework_TestCase {
    
    protected $presenter = NULL;

    protected function setUp() {
        global $container;
        
        $this->presenter = new \AdminModule\UsersPresenter($container);
    }

    /**
     * Presenter test
     * @return void
     */
    public function testPresenter() {
        $request = new \Nette\Application\Request('admin/users', 'GET', array());
        $response = $this->presenter->run($request);

        $this->assertInstanceOf('Nette\Application\Responses\TextResponse', $response);

    }
}