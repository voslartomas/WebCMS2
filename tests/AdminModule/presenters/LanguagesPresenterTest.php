<?php

class LanguagesPresenterTest extends \WebCMS\Tests\PresenterTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->createPresenter('Admin:Languages');
    }

    public function testDefault()
    {
        $response = $this->makeRequest();

        $this->assertInstanceOf('Nette\Application\Responses\TextResponse', $response);

        $this->getResponse($response);
    }

    public function testUpdateLanguage()
    {
        $response = $this->makeRequest('updateLanguage', 'GET', array(
            'id' => $this->language->getId(),
        ));

        $this->assertInstanceOf('Nette\Application\Responses\TextResponse', $response);

        $this->getResponse($response);
    }
}
