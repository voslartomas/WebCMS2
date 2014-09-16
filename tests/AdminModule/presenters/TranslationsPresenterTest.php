<?php

class TranslationsPresenterTest extends \WebCMS\Tests\PresenterTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->createPresenter('Admin:Translations');
    }

    public function testDefault()
    {
        $response = $this->makeRequest();

        $this->assertInstanceOf('Nette\Application\Responses\TextResponse', $response);

        $this->getResponse($response);
    }
}