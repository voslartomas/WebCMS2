<?php

class LanguageTest extends \WebCMS\Tests\EntityTestCase
{
    protected $language;

    public function testCreateLanguage()
    {
        $this->initLanguage();

        $this->em->persist($this->language);
        $this->em->flush();

        $languages = $this->em->getRepository('WebCMS\Entity\Language')->findAll();

        $this->assertEquals(1, count($languages));
        $this->assertEquals('cs', $languages[0]->getAbbr());
        $this->assertEquals(true, $languages[0]->getDefaultBackend());
        $this->assertEquals(true, $languages[0]->getDefaultFrontend());
        $this->assertEquals('utf-8', $languages[0]->getLocale());
        $this->assertEquals('Czech', $languages[0]->getName());
        $this->assertEquals(1, count($languages[0]->getTranslations()));
        $this->assertEquals('test', $languages[0]->getTranslations()[0]->getKey());

        $this->em->remove($languages[0]->getTranslations()[0]);
        $this->em->remove($languages[0]);

        $this->em->flush();

        $languages = $this->em->getRepository('WebCMS\Entity\Language')->findAll();

        $this->assertCount(0, $languages);
    }

    private function initLanguage()
    {
        $this->language = new \WebCMS\Entity\Language;
        $this->language->setAbbr('cs');
        $this->language->setDefaultBackend(true);
        $this->language->setDefaultFrontend(true);
        $this->language->setLocale('utf-8');
        $this->language->setName('Czech');

        $translations = array();

        $t = new \WebCMS\Entity\Translation();
        $t->setBackend(true);
        $t->setKey('test');
        $t->setTranslation('translation');

        $translations[] = $t;
        $this->language->setTranslations($translations);

        $t->setLanguage($this->language);
        $t->setHash();

        $this->em->persist($t);
    }
}
