<?php

class TranslatorTest extends \WebCMS\Tests\PresenterTestCase{
    
    public function testTranslator(){
	
	$translation = new \WebCMS\Translation\Translation($this->em, $this->language, TRUE);
	
	$translations = new \WebCMS\Translation\TranslationArray($translation);
	$translations['test'] = 'Translated text.';
	
	$translator = new WebCMS\Translation\Translator($translations);
	
	$translation = $translator->translate('key');
	$translation2 = $translator->translate('test');
	$translationWithParameters = $translator->translate('%s has %d apples.', array(
			'Tom',
			4
		));
	
	$this->assertEquals('key', $translation);
	$this->assertEquals('Translated text.', $translation2);
	$this->assertEquals('Tom has 4 apples.', $translationWithParameters);
    }
}
