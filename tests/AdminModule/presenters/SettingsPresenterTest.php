<?php

class SettingsPresenterTest extends \WebCMS\Tests\PresenterTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->createPresenter('Admin:Settings');
    }

    public function testDefault()
    {
        $response = $this->makeRequest();

        $this->assertInstanceOf('Nette\Application\Responses\TextResponse', $response);

        $this->getResponse($response);
    }

    public function testPictures()
    {
        $response = $this->makeRequest('pictures');

        $this->assertInstanceOf('Nette\Application\Responses\TextResponse', $response);

        $this->getResponse($response);
    }

    public function testThumbnailDialog()
    {
        $response = $this->makeRequest('addThumbnail');

        $this->assertInstanceOf('Nette\Application\Responses\TextResponse', $response);

        $this->getResponse($response);
    }

    public function testEmails()
    {
        $response = $this->makeRequest('emails');

        $this->assertInstanceOf('Nette\Application\Responses\TextResponse', $response);

        $this->getResponse($response);
    }

    public function testBoxesSettings()
    {
        $response = $this->makeRequest('boxesSettings');

        $this->assertInstanceOf('Nette\Application\Responses\TextResponse', $response);

        $this->getResponse($response);
    }

    public function testScriptsSettings()
    {
        $response = $this->makeRequest('scriptsSettings');

        $this->assertInstanceOf('Nette\Application\Responses\TextResponse', $response);

        $this->getResponse($response);
    }

    public function testStylesSettings()
    {
        $response = $this->makeRequest('stylesSettings');

        $this->assertInstanceOf('Nette\Application\Responses\TextResponse', $response);

        $this->getResponse($response);
    }

    public function testSeoSettings()
    {
        $response = $this->makeRequest('seoSettings');

        $this->assertInstanceOf('Nette\Application\Responses\TextResponse', $response);

        $this->getResponse($response);
    }

    public function testProjectSpecifics()
    {
        $response = $this->makeRequest('project');

        $this->assertInstanceOf('Nette\Application\Responses\TextResponse', $response);

        $this->getResponse($response);
    }

    public function testApi()
    {
        $response = $this->makeRequest('api');

        $this->assertInstanceOf('Nette\Application\Responses\TextResponse', $response);

        $this->getResponse($response);
    }
}
