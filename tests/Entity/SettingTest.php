<?php
    
class SettingTest extends \WebCMS\Tests\EntityTestCase {
    
    protected $setting; 
    
    public function testSetting() {
        
	$this->initSetting();
	
	$this->em->persist($this->setting);
	$this->em->flush();
	
	$settings = $this->em->getRepository('WebCMS\Entity\Setting')->findAll();
	
	$this->assertCount(1, $settings);
	$this->assertInstanceOf('WebCMS\Entity\Language', $settings[0]->getLanguage());
	$this->assertEquals('key', $settings[0]->getKey());
	$this->assertEquals('section', $settings[0]->getSection());
	$this->assertEquals(array('a' => 'b'), $settings[0]->getOptions());
	$this->assertEquals('type', $settings[0]->getType());
	$this->assertEquals('value', $settings[0]->getValue());
	
	$this->em->remove($settings[0]->getLanguage());
	$this->em->remove($settings[0]);
	
	$this->em->flush();
	
	$settings = $this->em->getRepository('WebCMS\Entity\Setting')->findAll();
	
	$this->assertEquals(0, count($settings));
    }
    
    private function initSetting(){
	
	$language = new WebCMS\Entity\Language;
	$language->setAbbr('en');
	$language->setDefaultBackend(true);
	$language->setDefaultFrontend(true);
	$language->setLocale('utf');
	$language->setName('Name');
	
	$this->em->persist($language);
	
	$this->setting = new WebCMS\Entity\Setting;
	$this->setting->setKey('key');
	$this->setting->setOptions(array('a' => 'b'));
	$this->setting->setSection('section');
	$this->setting->setType('type');
	$this->setting->setValue('value');
	$this->setting->setLanguage($language);
    }
}
