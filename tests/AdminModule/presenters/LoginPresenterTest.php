<?php

class LoginPresenterTest extends \WebCMS\Tests\EntityTestCase {
    
    protected $presenter = NULL;

    protected function setUp() {
        global $container;
        $this->presenter = new \AdminModule\LoginPresenter($container);
    }

    /**
     * Presenter test
     * @return void
     */
    public function testPresenter() {
        $request = new \Nette\Application\Request('/login', 'GET', array());
        $response = $this->presenter->run($request);

        $this->assertInstanceOf('Nette\Application\Responses\TextResponse', $response);

    }
}