<?php

namespace WebCMS\Tests;

abstract class PresenterTestCase extends EntityTestCase
{
    protected $presenter = NULL;

    protected $presenterName;

    protected $language;

    protected $role;

    public $user;

    public function setUp()
    {
        parent::setUp();

        // init system minimal
        $this->language = new \WebCMS\Entity\Language;
        $this->language->setName('English');
        $this->language->setAbbr('en');
        $this->language->setDefaultBackend(true);
        $this->language->setDefaultFrontend(true);
        $this->language->setLocale('en_US.utf8');

        $this->role = new \WebCMS\Entity\Role;
        $this->role->setName('superadmin');
        $this->role->setAutomaticEnable(FALSE);

        $this->em->persist($this->role);
        $this->em->flush();

        $this->user = new \WebCMS\Entity\User;
        $this->user->setUsername('test');
        $this->user->setPassword($this->container->authenticator->calculateHash('test'));
        $this->user->setEmail('test@test.com');
        $this->user->setName('test');
        $this->user->setRole($this->role);

        $this->em->persist($this->language);
        $this->em->persist($this->user);
        $this->em->flush();

        // login
        $user = new \Nette\Security\User($this->container->getService('nette.userStorage'), $this->container);
        $user->login('test', 'test');
    }

    public function tearDown()
    {
        parent::tearDown();

        system('rm -rf tests/temp/cache/* tests/temp/btfj.dat upload/* thumbnails/*');
    }

    /**
     * @param string $name
     */
    protected function createPresenter($name)
    {
        $this->presenterName = $name;

        $this->presenter = $this->container
                ->getByType('Nette\Application\IPresenterFactory')
                ->createPresenter($name);

        $this->presenter->autoCanonicalize = FALSE;
    }

    public function getResponse($response)
    {
        $template = $response->getSource();
        $template->registerHelperLoader('\WebCMS\Helpers\SystemHelper::loader');
        $template->setTranslator($this->presenter->translator);
        $template->settings = $this->presenter->settings;

        $template->save(__DIR__ . '/temp/cache/presenter.test');

        return file_get_contents(__DIR__ . '/temp/cache/presenter.test');
    }

    public function makeRequest($action = 'default', $method = 'GET', $params = array(), $post = array())
    {
        $params['action'] = $action;

        $request = new \Nette\Application\Request($this->presenterName, $method, $params, $post);

        return $this->presenter->run($request);
    }

    protected function createPage($module) 
    {
        $this->pageMain = new \WebCMS\Entity\Page;
        $this->pageMain->setParent(null);
        $this->pageMain->setLanguage($this->language);
        $this->pageMain->setModule(null);
        $this->pageMain->setModuleName('');
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

        $this->em->persist($this->pageMain);

        $this->page = new \WebCMS\Entity\Page;
        $this->page->setParent($this->pageMain);
        $this->page->setLanguage($this->language);
        $this->page->setModule(null);
        $this->page->setModuleName($module);
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
    }
}
