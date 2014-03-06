<?php

class UsersPresenterTest extends \WebCMS\Tests\PresenterTestCase {
    
    protected $presenter = NULL;

    protected function setUp() {
        $this->presenter = new \AdminModule\UsersPresenter($this->container);
    }

    /**
     * Presenter test
     * @return void
     */
    public function testPresenter() {
        $request = new Nette\Application\Request('admin/users', 'GET', array());
        $response = $this->presenter->run($request);

        $this->assertInstanceOf('Nette\Application\Responses\TextResponse', $response);

    }
}