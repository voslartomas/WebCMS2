<?php
    
class TranslationTest extends \WebCMS\Tests\EntityTestCase {
    
    protected $translation; 
    
    public function testTranslation() {
        
	$this->initTranslation();
	
	$this->em->persist($this->translation);
	$this->em->flush();
	
	$translations = $this->em->getRepository('WebCMS\Entity\Translation')->findAll();
	
	$hash = sha1($translations[0]->getKey() . $translations[0]->getLanguage()->getAbbr() . $translations[0]->getBackend());
	
	$this->assertCount(1, $translations);
	$this->assertTrue($translations[0]->getBackend());
	$this->assertInstanceOf('WebCMS\Entity\Language', $translations[0]->getLanguage());
	$this->assertEquals('Test', $translations[0]->getTranslation());
	$this->assertEquals($hash, $translations[0]->getHash());
	$this->assertEquals('key', $translations[0]->getKey());
	$this->assertTrue($translations[0]->getTranslated());
	
	$this->em->remove($translations[0]->getLanguage());
	$this->em->remove($translations[0]);
	
	$this->em->flush();
	
	$translations = $this->em->getRepository('WebCMS\Entity\Translation')->findAll();
	
	$this->assertEquals(0, count($translations));
    }
    
    private function initTranslation(){
	
	$language = new WebCMS\Entity\Language;
	$language->setAbbr('en');
	$language->setDefaultBackend(true);
	$language->setDefaultFrontend(true);
	$language->setLocale('utf');
	$language->setName('Name');
	
	$this->em->persist($language);
	
	$this->translation = new WebCMS\Entity\Translation;
	$this->translation->setBackend(true);
	$this->translation->setKey('key');
	$this->translation->setLanguage($language);
	$this->translation->setTranslation('Test');
	$this->translation->setHash();
    }
}