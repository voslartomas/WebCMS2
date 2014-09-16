<?php

class PageTest extends \WebCMS\Tests\EntityTestCase
{
    protected $pageMain;
    protected $page;
    protected $language;
    protected $module;
    protected $boxes;

    public function testCreatePage()
    {
        $this->initModule();
        $this->initLanguage();
        $this->initPages();

        $this->em->flush();

        $main = $this->em->getRepository('WebCMS\Entity\Page')->findOneByTitle('Main');

        $this->assertInstanceOf('WebCMS\Entity\Page', $main);
        $this->assertEquals(null, $main->getParent());
        $this->assertEquals($this->language, $main->getLanguage());
        $this->assertEquals($this->module, $main->getModule());
        $this->assertEquals('Module', $main->getModuleName());
        $this->assertEquals('meta title', $main->getMetaTitle());
        $this->assertEquals('meta description', $main->getMetaDescription());
        $this->assertEquals('meta keywords', $main->getMetaKeywords());
        $this->assertEquals('Main', $main->getTitle());
        $this->assertEquals('Presenter', $main->getPresenter());
        $this->assertEquals(true, $main->getVisible());
        $this->assertEquals(false, $main->getRedirect());
        $this->assertEquals('path/to/page', $main->getPath());
        $this->assertEquals(true, $main->getDefault());
        $this->assertEquals('', $main->getClass());
        $this->assertEquals('slug', $main->getSlug());
        $this->assertEquals(1, $main->getLeft());
        $this->assertEquals(4, $main->getRight());
        $this->assertInstanceOf('DateTime', $main->getCreated());
        $this->assertInstanceOf('DateTime', $main->getUpdated());

        $page = $this->em->getRepository('WebCMS\Entity\Page')->findOneByTitle('Home');

        $this->assertEquals('Main', $page->getParent()->getTitle());
        $this->assertInstanceOf('WebCMS\Entity\Box', $page->getBoxes()->first());
        $this->assertEquals('box1', $page->getBoxes()->first()->getBox());
        $this->assertEquals('box1', $page->getBox('box1')->getBox());
        $this->assertNull($page->getBox('box2'));
        $this->assertEquals('Home', $page->__toString());

        $selectTree = $this->em->getRepository('WebCMS\Entity\Page')->getTreeForSelect(array(array('by' => 'id', 'ord' => 'asc')), array('id > 0'));

        $this->assertEquals(array(1 => 'Main', 2 => '-Home'), $selectTree);

        $this->em->remove($page->getBoxes()->first());
        $this->em->remove($main);
        $this->em->remove($page);

        $main->toArray();

        $this->em->flush();

        $pages = $this->em->getRepository('WebCMS\Entity\Page')->findAll();

        $this->assertEquals(0, count($pages));
    }

    private function initPages()
    {
        $this->pageMain = new \WebCMS\Entity\Page;
        $this->pageMain->setParent(null);
        $this->pageMain->setLanguage($this->language);
        $this->pageMain->setModule($this->module);
        $this->pageMain->setModuleName('Module');
        $this->pageMain->setMetaTitle('meta title');
        $this->pageMain->setMetaDescription('meta description');
        $this->pageMain->setMetaKeywords('meta keywords');
        $this->pageMain->setTitle('Main');
        $this->pageMain->setPresenter('Presenter');
        $this->pageMain->setVisible(true);
        $this->pageMain->setRedirect(false);
        $this->pageMain->setPath('path/to/page');
        $this->pageMain->setDefault(true);
        $this->pageMain->setClass('');
        $this->pageMain->setSlug('slug');

        $this->em->persist($this->pageMain);

        $this->page = new \WebCMS\Entity\Page;
        $this->page->setParent($this->pageMain);
        $this->page->setLanguage($this->language);
        $this->page->setModule($this->module);
        $this->page->setModuleName('Module');
        $this->page->setMetaTitle('meta title');
        $this->page->setMetaDescription('meta description');
        $this->page->setMetaKeywords('meta keywords');
        $this->page->setTitle('Home');
        $this->page->setPresenter('Presenter');
        $this->page->setVisible(true);
        $this->page->setRedirect(false);
        $this->page->setPath('path/to/home');
        $this->page->setDefault(true);
        $this->page->setClass('class');

        $this->em->persist($this->page);

        $this->initBoxes();

        // set boxes
        $this->page->setBoxes($this->boxes);
    }

    private function initModule()
    {
        $this->module = new WebCMS\Entity\Module;
        $this->module->setActive(true);
        $this->module->setName('Module');
        $this->module->setPresenters(array());

        $this->em->persist($this->module);
    }

    private function initLanguage()
    {
        $this->language = new \WebCMS\Entity\Language;
        $this->language->setAbbr('cs');
        $this->language->setDefaultBackend(true);
        $this->language->setDefaultFrontend(true);
        $this->language->setLocale('utf-8');
        $this->language->setName('Czech');

        $this->em->persist($this->language);
    }

    private function initBoxes()
    {
        $box = new WebCMS\Entity\Box;
        $box->setBox('box1');
        $box->setFunction('function');
        $box->setModuleName('Module');
        $box->setPageFrom($this->pageMain);
        $box->setPageTo($this->page);
        $box->setPresenter('Presenter');

        $this->em->persist($box);

        $this->boxes[] = $box;
    }
}
