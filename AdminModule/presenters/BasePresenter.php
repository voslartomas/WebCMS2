<?php

namespace AdminModule;

use Nette;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use Nette\Application\UI;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * Base class for all application presenters.
 * TODO refactoring
 * @author     Tomáš Voslař <tomas.voslar at webcook.cz>
 * @package    WebCMS2
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    /** @var Doctrine\ORM\EntityManager */
    protected $em;

    /* @var \WebCMS\Translation\Translation */
    public $translation;

    /* @var \WebCMS\Translation\Translator */
    public $translator;

    /* @var Nette\Http\SessionSection */
    public $state;

    /* @var User */
    public $systemUser;

    /* @var \WebCMS\Settings */
    public $settings;

    /* @var Page */
    public $actualPage;

    /* @var \WebCMS\Helpers\PriceFormatter */
    public $priceFormatter;

    /* Method is executed before render. */
    protected function beforeRender()
    {
        $this->setLayout("layout");

        if ($this->isAjax()) {
            $this->invalidateControl('flashMessages');
        }

        // boxes settings, only if page is module
        if ($this->getParam('id')) {
            $this->template->boxesSettings = TRUE;
        } else {
            $this->template->boxesSettings = FALSE;
        }

        // check filled info email
        $infoEmail = $this->settings->get('Info email', \WebCMS\Settings::SECTION_BASIC)->getValue();
        if (!Nette\Utils\Validators::isEmail($infoEmail)) {
            $this->flashMessage('Please fill in correct info email. Settings -> Basic settings.', 'warning');
        }

        
        if (!$this->isAjax()) {
            $this->template->structures = $this->getStructures(); // TODO add function for AJAX and normal request it is not necessary to load everything
        }
        
        $this->template->registerHelperLoader('\WebCMS\Helpers\SystemHelper::loader');
        $this->template->actualPage = $this->actualPage;
        $this->template->user = $this->getUser();
        $this->template->setTranslator($this->translator);
        $this->template->language = $this->state->language;
        $this->template->version = \WebCMS\Helpers\SystemHelper::getVersion();
        $this->template->activePresenter = $this->getPresenter()->getName();
        $this->template->settings = $this->settings;
        $this->template->languages = $this->em->getRepository('WebCMS\Entity\Language')->findAll();
    }

    /**
     * 
     */
    private function processLanguage() 
    {
        $this->state = $this->getSession('admin');

        // changing language
        if ($this->getParameter('language_id_change')) {
            $this->state->language = $this->em->find('WebCMS\Entity\Language', $this->getParameter('language_id_change'));
            $this->forward(':Admin:Homepage:default');
        }

        if (!isset($this->state->language)) {
            $this->state->language = $this->em->getRepository('WebCMS\Entity\Language')->findOneBy(array(
            'defaultBackend' => 1
            ));
        }

        $language = $this->em->find('WebCMS\Entity\Language', $this->state->language->getId());
        // check whether is language still in db
        if (!$language) {
            unset($this->state->language);
            $this->forward('Homepage:default');
        }

        // reload entity from db
        $this->state->language = $this->em->find('WebCMS\Entity\Language', $this->state->language->getId());

        \WebCMS\Helpers\PriceFormatter::setLocale($this->state->language->getLocale());

        // translations
        $default = $this->em->getRepository('WebCMS\Entity\Language')->findOneBy(array(
            'defaultBackend' => 1
        ));

        $translation = new \WebCMS\Translation\Translation($this->em, $default, 1);
        $this->translation = $translation->getTranslations();
        $this->translator = new \WebCMS\Translation\Translator($this->translation);
    }

    /* Startup method. */
    protected function startup()
    {
        parent::startup();

        if (!$this->getUser()->isLoggedIn() && $this->presenter->getName() !== "Admin:Login") {
            $this->forward(':Admin:Login:');
        }

        $this->processLanguage();

        // system settings
        $this->settings = new \WebCMS\Settings($this->em, $this->state->language);
        $this->settings->setSettings($this->getSettings());

        // system helper sets variables
        \WebCMS\Helpers\SystemHelper::setVariables(array(
            'baseUrl' => $this->presenter->getHttpRequest()->url->baseUrl,
            'infoEmail' => $this->settings->get('Info email', 'basic')->getValue()
        ));

        // price formatting
        $this->priceFormatter = new \WebCMS\Helpers\PriceFormatter($this->state->language->getLocale());

        $id = $this->getParam('idPage');
        if ($id) {
            $this->actualPage = $this->em->find('WebCMS\Entity\Page', $id);
        }

        $this->checkPermission();

        $this->logAction();
    }

    private function logAction() 
    {
        // Create the logger
        $logger = new Logger('History');
        // Now add some handlers
        $parameters = $this->getContext()->getParameters();

        $logger->pushHandler(new StreamHandler($parameters['tempDir'] . '/../log/webcms.log', Logger::DEBUG));

        $data = array(
            'user' => $this->getUser()->getIdentity() ? $this->getUser()->getIdentity()->getData()['username'] : 'unknown',
            'action' => $this->getAction(),
            'presenter' => $this->getName(),
            'title' => is_object($this->actualPage) ? $this->actualPage->getTitle() : 'System',
            'url' => $this->getHttpRequest()->url->absoluteUrl
        );

        if ($this->getName() === 'Admin:Homepage' || $this->getName() === 'Admin:Login') {
            $logger->addNotice('Login', $data);
        } else {
            $logger->addInfo('Request catcher', $data);
        }
    }

    private function getSettings()
    {
        $query = $this->em->createQuery('SELECT s FROM WebCMS\Entity\Setting s WHERE s.language >= ' . $this->state->language->getId() . ' OR s.language IS NULL');
        $tmp = $query->getResult();

        $settings = array();
        foreach ($tmp as $s) {
            $settings[$s->getSection()][$s->getKey()] = $s;
        }

        return $settings;
    }

    protected function createSettingsForm($settings)
    {
        $form = $this->createForm();

        if (!$settings) {
            return $form;
        }

        foreach ($settings as $s) {
            $ident = $s->getId();

            if ($s->getType() === 'text' || $s->getType() === null) {
                $form->addText($ident, $s->getKey())->setDefaultValue($s->getValue())->setAttribute('class', 'form-control');
            } elseif ($s->getType() === 'textarea')
            $form->addTextArea($ident, $s->getKey())->setDefaultValue($s->getValue())->setAttribute('class', 'editor');
            elseif ($s->getType() === 'radio') {
                $form->addRadioList($ident, $s->getKey(), $s->getOptions())->setDefaultValue($s->getValue());
            } elseif ($s->getType() === 'select') {
                $form->addSelect($ident, $s->getKey(), $s->getOptions())->setDefaultValue($s->getValue());
            } elseif ($s->getType() === 'checkbox') {
                $form->addCheckbox($ident, $s->getKey())->setDefaultValue($s->getValue());
            }
        }

        $form->addSubmit('submit', 'Save settings');
        $form->onSuccess[] = callback($this, 'settingsFormSubmitted');

        return $form;
    }

    public function settingsFormSubmitted(\Nette\Application\UI\Form $form)
    {
        $values = $form->getValues();

        foreach ($values as $key => $v) {
            $setting = $this->em->find('WebCMS\Entity\Setting', $key);
            $setting->setValue($v);
        }

        $this->em->flush();

        $this->flashMessage('Settings has been saved.', 'success');
        $this->forward('this');
    }

    /* Invalidate ajax content. */

    protected function reloadContent()
    {
        if ($this->isAjax()) {
            $this->invalidateControl('content');
        }
    }

    /* Invalidate ajax modal content. */

    protected function reloadModalContent()
    {
        if ($this->isAjax()) {
            $this->invalidateControl('modalContent');
        }
    }

    /**
     * Creates default basic grid.
     * @param  Nette\Application\UI\Presenter $presenter
     * @param  String                         $name
     * @param  String                         $entity
     * @param string[] $where
     * @return \Grido\Grid
     */
    public function createGrid(Nette\Application\UI\Presenter $presenter, $name, $entity, $order = NULL, $where = NULL)
    {
        $grid = new \Grido\Grid($presenter, $name);

        $qb = $this->em->createQueryBuilder();

        if ($order) {
            foreach ($order as $o) {
            $qb->addOrderBy('l.' . $o['by'], $o['dir']);
            }
        }

        if ($where) {
            foreach ($where as $w) {
            $qb->andWhere('l.' . $w);
            }
        }

        if (strpos($entity, 'WebCMS') === false) {
            $grid->setModel($qb->select('l')->from("WebCMS\Entity\\$entity", 'l'));
        } else {
            $grid->setModel($qb->select('l')->from($entity, 'l'));
        }

        $grid->setRememberState(true);
        $grid->setDefaultPerPage(10);
        $grid->setTranslator($this->translator);
        $grid->setFilterRenderType(\Grido\Components\Filters\Filter::RENDER_INNER);

        return $grid;
    }

    /**
     * Creates form and rewrite renderer for bootstrap.
     * @return UI\Form
     */
    public function createForm()
    {
        $form = new Nette\Application\UI\Form();

        $form->getElementPrototype()->addAttributes(array('class' => 'ajax'));
        $form->setTranslator($this->translator);
        $form->setRenderer(new BootstrapRenderer);

        return $form;
    }

    /**
     * Injects entity manager.
     * @param  \Doctrine\ORM\EntityManager  $em
     * @return BasePresenter
     * @throws \Nette\InvalidStateException
     */
    public function injectEntityManager(\Doctrine\ORM\EntityManager $em)
    {
        if ($this->em) {
            throw new \Nette\InvalidStateException('Entity manager has been already set.');
        }

        $this->em = $em;

        return $this;
    }

    /**
     * TODO refactoring
     */
    private function checkPermission()
    {
        // checking permission of user
        $acl = new Nette\Security\Permission;

        // roles
        $roles = $this->em->getRepository("WebCMS\Entity\Role")->findAll();

        $acl->addRole('guest');
        foreach ($roles as $r) {
            $acl->addRole($r->getName());
        }

        // resources definition
        $res = \WebCMS\Helpers\SystemHelper::getResources();

        // pages resources
        $pages = $this->em->getRepository('WebCMS\Entity\Page')->findAll();

        foreach ($pages as $page) {
            if ($page->getParent() != NULL) {

            $module = $this->createObject($page->getModuleName());

            foreach ($module->getPresenters() as $presenter) {
                $key = 'admin:' . $page->getModuleName() . '' . $presenter['name'] . $page->getId();
                $res[$key] = $page->getTitle();
            }
            }
        }

        $acl->addResource('admin:Homepage');
        $acl->addResource('admin:Login');
        foreach ($res as $key => $r) {
            $acl->addResource($key);
        }

        // resources
        $identity = $this->getUser()->getIdentity();
        if (is_object($identity))
            $permissions = $identity->data['permissions'];
        else
            $permissions = array();

        foreach ($permissions as $key => $p) {
            if ($p && $acl->hasResource($key))
            $acl->allow($identity->roles[0], $key, Nette\Security\Permission::ALL);
        }

        // homepage and login page can access everyone
        $acl->allow(Nette\Security\Permission::ALL, 'admin:Homepage', Nette\Security\Permission::ALL);
        $acl->allow(Nette\Security\Permission::ALL, 'admin:Login', Nette\Security\Permission::ALL);

        // superadmin can do everything
        $acl->allow('superadmin', Nette\Security\Permission::ALL, Nette\Security\Permission::ALL);

        $roles = $this->getUser()->getRoles();

        $hasRigths = false;
        $check = false;

        if (substr_count(lcfirst($this->name), ':') == 2)
            $resource = \WebCMS\Helpers\SystemHelper::strlReplace(':', '', lcfirst($this->name) . $this->getParam('idPage'));
        else
            $resource = lcfirst($this->name);

        foreach ($roles as $role) {
            $check = $acl->isAllowed($role, $resource, $this->action);

            if ($check)
            $hasRigths = true;
        }

        if (!$hasRigths) {
            $this->presenter->flashMessage($this->translation['You do not have a permission to do this operation!'], 'danger');
            $this->redirect(":Admin:Homepage:");
        }
    }

    protected function createObject($name)
    {
        $expl = explode('-', $name);

        $objectName = ucfirst($expl[0]);
        $objectName = "\\WebCMS\\$objectName" . "Module\\" . $objectName;

        return new $objectName;
    }

    private function getStructures()
    {
        $qb = $this->em->createQueryBuilder();

        $qb->addOrderBy('l.root', 'ASC');
        $qb->andWhere('l.parent IS NULL');
        $qb->andWhere('l.language = ' . $this->state->language->getId());

        return $qb->select('l')->from("WebCMS\Entity\Page", 'l')->getQuery()->getResult();
    }

    /* SEO SETTINGS */

    public function renderSeo($idPage)
    {
        $this->reloadContent();

        $this->template->actualPage = $this->actualPage;
        $this->template->idPage = $idPage;
    }

    public function createComponentSeoForm()
    {
        $form = $this->createForm();

        $form->addText('slug', 'Seo url')->setAttribute('class', 'form-control');
        $form->addText('metaTitle', 'Seo title')->setAttribute('class', 'form-control');
        $form->addText('metaKeywords', 'Seo keywords')->setAttribute('class', 'form-control');
        $form->addText('metaDescription', 'Seo description')->setAttribute('class', 'form-control');
        $form->addSubmit('send', 'Save')->setAttribute('class', 'btn btn-success');
        $form->onSuccess[] = callback($this, 'seoFormSubmitted');

        $form->setDefaults($this->actualPage->toArray());

        return $form;
    }

    public function seoFormSubmitted($form)
    {
        $values = $form->getValues();

        if (empty($values->slug)) {
            $this->actualPage->setSlug(NULL);
        } else {
            $this->actualPage->setSlug($values->slug);
        }
        $this->actualPage->setMetaTitle($values->metaTitle);
        $this->actualPage->setMetaKeywords($values->metaKeywords);
        $this->actualPage->setMetaDescription($values->metaDescription);

        $path = $this->em->getRepository('WebCMS\Entity\Page')->getPath($this->actualPage);
        $final = array();
        foreach ($path as $p) {
            if ($p->getParent() != NULL)
            $final[] = $p->getSlug();
        }

        $this->actualPage->setPath(implode('/', $final));

        $this->em->flush();

        $this->flashMessage('Seo settings has been saved.', 'success');
        $this->forward('this');
    }

    /* BOXES SETTINGS */

    public function renderBoxes($idPage)
    {
        $this->reloadContent();

        $parameters = $this->getContext()->container->getParameters();
        $boxes = $parameters['boxes'];

        /*
        foreach ($boxes as &$box) {
            //$box['component'] = $id . '-' . $box['presenter'] . '-' . $box['function'];
        }*/
        if (!is_array($boxes)) {
            $boxes = array();
        }

        $this->template->actualPage = $this->actualPage;
        $this->template->boxes = $boxes;
        $this->template->idPage = $idPage;
    }

    public function createComponentBoxesForm()
    {
        $form = $this->createForm();

        $parameters = $this->getContext()->container->getParameters();
        $boxes = $parameters['boxes'];

        $pages = $this->em->getRepository('WebCMS\Entity\Page')->findBy(array(
            'language' => $this->state->language
        ));

        $boxesAssoc = array();
        foreach ($pages as $page) {
            if ($page->getParent() != NULL) {
            $module = $this->createObject($page->getModuleName());

            foreach ($module->getBoxes() as $box) {
                $boxesAssoc[$page->getId() . '-' . $box['module'] . '-' . $box['presenter'] . '-' . $box['function']] = $page->getTitle() . ' - ' . $this->translation[$box['name']];
            }
            }
        }

        $boxesAssoc = array(
            0 => $this->translation['Box is not linked.']
            ) + $boxesAssoc;

        if (!empty($boxes)) {
            foreach ($boxes as $name => $active) {
            $form->addSelect($name, $name, $boxesAssoc)
                ->setTranslator(NULL)
                ->setAttribute('class', 'form-control');
            }
        }

        // set defaults
        $boxes = $this->em->getRepository('WebCMS\Entity\Box')->findBy(array(
            'pageTo' => $this->actualPage
        ));

        $defaults = array();
        if (!empty($boxes)) {
            foreach ($boxes as $box) {
            if (is_object($box->getPageFrom()))
                $defaults[$box->getBox()] = $box->getPageFrom()->getId() . '-' . $box->getModuleName() . '-' . $box->getPresenter() . '-' . $box->getFunction();
            }
        }

        $form->setDefaults($defaults);
        $form->addSubmit('submit', 'Save');
        $form->onSuccess[] = callback($this, 'boxesFormSubmitted');

        return $form;
    }

    public function boxesFormSubmitted(UI\Form $form)
    {
        $values = $form->getValues();

        // delete old asscociations
        $q = $this->em->createQuery('delete from WebCMS\Entity\Box m where m.pageTo = ' . $this->actualPage->getId());
        $numDeleted = $q->execute();

        // persist new associations
        foreach ($values as $key => $value) {
            if ($value) {
            $params = explode('-', $value);

            $pageFrom = $this->em->find('WebCMS\Entity\Page', $params[0]);

            $box = new \WebCMS\Entity\Box();
            $box->setPageFrom($pageFrom);
            $box->setPageTo($this->actualPage);
            $box->setModuleName($params[1]);
            $box->setPresenter($params[2]);
            $box->setFunction($params[3]);
            $box->setBox($key);

            $this->em->persist($box);
            }
        }

        $this->em->flush();

        $this->flashMessage('Boxes settings has been saved.', 'success');
        $this->forward('this');
    }

    public function handleAddToFavourite($link, $title)
    {
        $user = $this->em->getRepository('WebCMS\Entity\User')->find($this->getUser()->getId());

        $fav = new \WebCMS\Entity\Favourites();
        $fav->setLink($link);
        $fav->setUser($user);
        $fav->setTitle($title);

        $this->em->persist($fav);

        $this->em->flush();

        $this->flashMessage('Link has been added to favourites.', 'success');
        }

        public function handleGetTranslations()
        {
        $payload = array();
        foreach ($_GET['keys'] as $key => $value) {
            $payload[$key] = $this->translator->translate($key);
        }

        $this->sendResponse(new \Nette\Application\Responses\JsonResponse($payload));
    }

    public function handleRemoveFromFavourite($idFav)
    {
        $fav = $this->em->getRepository('WebCMS\Entity\Favourites')->find($idFav);

        $this->em->remove($fav);
        $this->em->flush();

        $this->flashMessage('Link has been removed from favourites.', 'success');
    }

    public function flashMessage($text, $type = 'info')
    {
        parent::flashMessage($this->translation[$text], $type);
    }

}
