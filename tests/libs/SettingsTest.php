<?php

class SettingsTest extends \WebCMS\Tests\EntityTestCase
{
    public function testSettings()
    {
        $language = new WebCMS\Entity\Language();
        $language->setName('English');
        $language->setAbbr('en');
        $language->setDefaultFrontend(true);
        $language->setDefaultBackend(true);
        $language->setLocale('en_US.utf8');

        $this->em->persist($language);
        $this->em->flush();

        $settings = new \WebCMS\Settings($this->em, $language);

        $settings->get('testKey', 'testSection', null, array(
            1 => 'first',
            2 => 'second',
        ), true);

        $setting = $settings->get('testKey', 'testSection', 'option', array(
            1 => 'first',
            2 => 'second',
        ), true);

        $settings->setLanguage(1);

        $falseSection = $settings->getSection('asd', 'unknown');
        $this->assertFalse($falseSection);

        $settings->setSettings(array('testSection' => array(0 => $setting)));
        $section = $settings->getSection('testSection');

        $this->assertEquals('testKey', $setting->getKey());
        $this->assertEquals('testSection', $setting->getSection());
        $this->assertEquals(array(
            1 => 'first',
            2 => 'second',
        ), $setting->getOptions());
        $this->assertEquals(array('testSection' => array(0 => $setting)), $settings->getSettings());
        $this->assertEquals(array(0 => $setting), $section);
    }
}
