<?php

namespace WebCMS\Tests;

class TranslationTest extends \WebCMS\Tests\PresenterTestCase
{
    public function testTranslation()
    {
	    $translations = new \WebCMS\Translation\Translation($this->em, $this->language, TRUE);

	    $translations->addTranslation('Test translation', 'Translated text');

	    $translation = $translations->getTranslationByKey('Test translation');

	    $this->assertEquals('Translated text', $translation);
	    $this->assertInstanceOf('WebCMS\Translation\TranslationArray', $translations->getTranslations());
    }
}
