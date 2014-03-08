<?php

class TranslationArrayTest extends \WebCMS\Tests\PresenterTestCase{
    
    protected $translationArray;
    
    public function setUp(){
	parent::setUp();
	
	$translation = new \WebCMS\Translation\Translation($this->em, $this->language, TRUE);
	
	$this->translationArray = new WebCMS\Translation\TranslationArray($translation);
    }
    
    public function testTranslationArrayData(){
	
	$data = array(
	    'key' => 'Translated',
	    'key2' => 'Translated second'
	);
	
	$this->translationArray->setData($data);
	
	$this->assertEquals($data, $this->translationArray->getData());
    }
    
    public function testAddTranslation(){
	
	$this->translationArray['key'];
	
	$this->assertEquals('key', $this->translationArray['key']);
	
	$this->translationArray['key'] = 'Translated';
	
	$this->assertEquals('Translated', $this->translationArray['key']);
    }
}