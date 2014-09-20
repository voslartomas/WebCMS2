<?php

class TranslatorPresenterTest extends \WebCMS\Tests\PresenterTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->createPresenter('Admin:Translator');
    }

    public function testDefault()
    {
        $response = $this->makeRequest();

        $this->assertInstanceOf('Nette\Application\Responses\TextResponse', $response);

        $this->getResponse($response);
    }
}