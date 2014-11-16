<?php

class CloningPresenterTest extends \WebCMS\Tests\PresenterTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->createPresenter('Admin:Cloning');
    }

    public function testDefault()
    {
        $response = $this->makeRequest();

        $this->assertInstanceOf('Nette\Application\Responses\TextResponse', $response);

        $this->getResponse($response);
    }
}
