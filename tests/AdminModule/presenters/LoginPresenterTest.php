<?php

class LoginPresenterTest extends \WebCMS\Tests\PresenterTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->createPresenter('Admin:Login');
    }

    public function testDefault()
    {
        $this->presenter->getUser()->logout();

        $response = $this->makeRequest();

            $this->assertInstanceOf('Nette\Application\Responses\TextResponse', $response);

        $this->getResponse($response);
    }

    public function testRedirection()
    {
        $response = $this->makeRequest();

        $this->assertInstanceOf('Nette\Application\Responses\RedirectResponse', $response);
    }

    public function testLogin()
    {
        $this->presenter->getUser()->logout();

        $response = $this->makeRequest('default', 'POST', array(
            'action' => 'default',
            'do' => 'signInForm-submit',
            ), array(
            'username' => 'test',
            'password' => 'test',
            'remember' => '1',
            'send' => 'Log in'
        ));

        $this->assertInstanceOf('Nette\Application\Responses\RedirectResponse', $response);
    }

    public function testBadLogin()
    {
        $this->presenter->getUser()->logout();

        $response = $this->makeRequest('default', 'POST', array(
            'action' => 'default',
                'do' => 'signInForm-submit',
        ), array(
            'username' => 'test',
            'password' => 'badpassword',
            'remember' => '1',
            'send' => 'Log in'
        ));

        $this->assertInstanceOf('Nette\Application\Responses\TextResponse', $response);
    }
}
