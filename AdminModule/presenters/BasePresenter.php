<?php

namespace AdminModule;

use Nette;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use Nette\Application\UI;

/**
 * Base class for all application presenters.
 * TODO refactoring
 * @author     Tomáš Voslař <tomas.voslar at webcook.cz>
 * @package    WebCMS2
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter{
	/** @var Doctrine\ORM\EntityManager */
	protected $em;
	
	/* @var \WebCMS\Translation */
	public $translation;
	
	/* @var \WebCMS\Translator */
	public $translator;
	
	/* @var Nette\Http\SessionSection */
	public $state;
	
	/* @var User */
	public $systemUser;
	
	/* @var \WebCMS\Settings */
	public $settings;
	
	/* @var Page */
	public $actualPage;
	
	/* Method is executed before render. */
	protected function beforeRender(){
		
		$this->setLayout("layout");
		
		if($this->isAjax()){
			$this->invalidateControl('flashMessages');
		}
		
		// boxes settings, only if page is module
		if($this->getParam('id')){
			$this->template->boxesSettings = TRUE;
		}else{
			$this->template->boxesSettings = FALSE;
		}
		
		$this->template->registerHelperLoader('\WebCMS\SystemHelper::loader');
		$this->template->actualPage = $this->actualPage;
		$this->template->structures = $this->getStructures();
		$this->template->user = $this->getUser();
		$this->template->setTranslator($this->translator);
		$this->template->language = $this->state->language;
		$this->template->version = \WebCMS\SystemHelper::getVersion();
		$this->template->activePresenter = $this->getPresenter()->getName();
		$this->template->languages = $this->em->getRepository('AdminModule\Language')->findAll();
	}
	
	/* Startup method. */
	protected function startup(){
		parent::startup();
		
		if (!$this->getUser()->isLoggedIn() && $this->presenter->getName() !== "Admin:Login") {
			$this->redirect(':Admin:Login:');
		}
		
		$this->state = $this->getSession('admin');

		// changing language
		if($this->getParameter('language_id_change')){
			$this->state->language = $this->em->find('AdminModule\Language', $this->getParameter('language_id_change'));
			$this->redirect(':Admin:Homepage:default');
		}
		
		if(!isset($this->state->language)){
			$this->state->language = $this->em->getRepository('AdminModule\Language')->findOneBy(array(
				'defaultBackend' => 1
			));
		}
		
		$language = $this->em->find('AdminModule\Language', $this->state->language->getId());
		// check whether is language still in db
		if(!$language){
			unset($this->state->language);
			$this->redirect('Homepage:default');
		}
		
		// reload entity from db
		$this->state->language = $this->em->find('AdminModule\Language', $this->state->language->getId());
		
		// translations
		$translation = new \WebCMS\Translation($this->em, $language , 1);
		$this->translation = $translation->getTranslations();
		$this->translator = new \WebCMS\Translator($this->translation);
		
		// system settings
		$this->settings = new \WebCMS\Settings($this->em, $this->state->language);
		$this->settings->setSettings($this->getSettings());
		
		$id = $this->getParam('id');
		if($id) $this->actualPage = $this->em->find('AdminModule\Page', $id);
		
		$this->checkPermission();
	}
	
	private function getSettings(){
		$query = $this->em->createQuery('SELECT s FROM AdminModule\Setting s WHERE s.language >= ' . $this->state->language->getId() . ' OR s.language IS NULL');
		$tmp = $query->getResult();	
		
		$settings = array();
		foreach($tmp as $s){
			$settings[$s->getSection()][$s->getKey()] = $s;
		}
		
		return $settings;
	}
	
	/* Invalidate ajax content. */
	protected function reloadContent(){
		if($this->isAjax()){
			$this->invalidateControl('content');
		}
	}
	
	/* Invalidate ajax modal content. */
	protected function reloadModalContent(){
		if($this->isAjax()){
			$this->invalidateControl('modalContent');
		}
	}
	
	/**
	 * Creates default basic grid.
	 * @param Nette\Application\UI\Presenter $presenter
	 * @param String $name
	 * @param String $entity
	 * @return \Grido\Grid
	 */
	public function createGrid(Nette\Application\UI\Presenter $presenter, $name, $entity, $order = NULL, $where = NULL){
		$grid = new \Grido\Grid($presenter, $name);
		
		$qb = $this->em->createQueryBuilder();
		
		if($order){
			foreach($order as $o){
				$qb->addOrderBy('l.' . $o['by'], $o['dir']);
			}
		}
		
		if($where){
			foreach($where as $w){
				$qb->andWhere('l.' . $w);
			}
		}
		
		$grid->setModel($qb->select('l')->from("AdminModule\\$entity", 'l'));
		//$grid->setRememberState();
		$grid->setTranslator($this->translator);
		$grid->setFilterRenderType(\Grido\Components\Filters\Filter::RENDER_INNER);
		
		return $grid;
	}
	
	/**
	 * Creates form and rewrite renderer for bootstrap.
	 * @return type
	 */
	public function createForm(){
		$form = new Nette\Application\UI\Form();
		
		$form->getElementPrototype();//->addAttributes(array('class' => 'ajax'));
		$form->setTranslator($this->translator);
		$form->setRenderer(new BootstrapRenderer);
		
		return $form;
	}
	
	/**
	 * Injects entity manager.
	 * @param \Doctrine\ORM\EntityManager $em
	 * @return \Backend\BasePresenter
	 * @throws \Nette\InvalidStateException
	 */
	public function injectEntityManager(\Doctrine\ORM\EntityManager $em){
		if ($this->em) {
			throw new \Nette\InvalidStateException('Entity manager has been already set.');
		}
		
		$this->em = $em;
		return $this;
	}
	
	/**
	 * TODO refactoring
	 */
	private function checkPermission(){
		// checking permission of user
		$acl = new Nette\Security\Permission;
		
		// roles
		$roles = $this->em->getRepository("AdminModule\Role")->findAll();
		
		$acl->addRole('guest');
		foreach($roles as $r){
			$acl->addRole($r->getName());
		}
		
		// resources definition
		$res = \WebCMS\SystemHelper::getResources();
		
		// pages resources
		$pages = $this->em->getRepository('AdminModule\Page')->findAll();
		
		foreach($pages as $page){
			if($page->getParent() != NULL){
				
				$module = $this->createObject($page->getModuleName());
				
				foreach($module->getPresenters() as $presenter){
					$key = 'admin:' . $page->getModuleName() . '' . $presenter['name'] . $page->getId();
					$res[$key] = $page->getTitle();
				}
			}
		}
		
		$acl->addResource('admin:Homepage');
		$acl->addResource('admin:Login');
		foreach($res as $key => $r){
			$acl->addResource($key);
		}
		
		// resources
		$identity = $this->getUser()->getIdentity();
		if(is_object($identity)) $permissions = $identity->data['permissions'];
		else $permissions = array();

		foreach($permissions as $key => $p){
			if($p) $acl->allow($identity->roles[0], $key, Nette\Security\Permission::ALL);
		}
		
		// homepage and login page can access everyone
		$acl->allow(Nette\Security\Permission::ALL, 'admin:Homepage', Nette\Security\Permission::ALL);
		$acl->allow(Nette\Security\Permission::ALL, 'admin:Login', Nette\Security\Permission::ALL);
		
		// superadmin can do everything
		$acl->allow('superadmin', Nette\Security\Permission::ALL, Nette\Security\Permission::ALL);
		
		$roles = $this->getUser()->getRoles();
		
		$hasRigths = false;
		$check = false;
		
		if(substr_count(lcfirst($this->name), ':') == 2) $resource = \WebCMS\SystemHelper::strlReplace(':', '', lcfirst($this->name) . $this->getParam('id'));
		else $resource = lcfirst($this->name);

		foreach ($roles as $role) {
			$check = $acl->isAllowed($role, $resource, $this->action);
			
			if($check)
				$hasRigths = true;
		}
		
		if(!$hasRigths){
			$this->presenter->flashMessage($this->translation['You do not have a permission to do this operation!'], 'danger');
			$this->redirect(":Admin:Homepage:");
		}
	}
	
	protected function createObject($name){
		$expl = explode('-', $name);

		$objectName = ucfirst($expl[0]);
		$objectName = "\\WebCMS\\$objectName" . "Module\\" . $objectName;
		
		return new $objectName;
	}
	
	
	
	private function getStructures(){
		$qb = $this->em->createQueryBuilder();
		
		$qb->addOrderBy('l.root', 'ASC');
		$qb->andWhere('l.parent IS NULL');
		$qb->andWhere('l.language = ' . $this->state->language->getId());
		
		return $qb->select('l')->from("AdminModule\\Page", 'l')->getQuery()->getResult();
	}
	
	/* BOXES SETTINGS */
	
	public function renderBoxes($id){
		
		$this->reloadContent();
		
		$parameters = $this->getContext()->container->getParameters();
		$boxes = $parameters['boxes'];
		
		foreach($boxes as &$box){
			//$box['component'] = $id . '-' . $box['presenter'] . '-' . $box['function'];
		}
		
		$this->template->boxes = $boxes;
		$this->template->id = $id;
	}
	
	public function createComponentBoxesForm(){
		$form = $this->createForm();
		
		$parameters = $this->getContext()->container->getParameters();
		$boxes = $parameters['boxes'];
		
		$pages = $this->em->getRepository('AdminModule\Page')->findBy(array(
			'language' => $this->state->language
		)); 
		
		$boxesAssoc = array();
		foreach($pages as $page){
			if($page->getParent() != NULL){
				$module = $this->createObject($page->getModuleName());

				foreach($module->getBoxes() as $box){
					$boxesAssoc[$page->getId() . '-' . $box['presenter'] . '-' . $box['function']] = $page->getTitle() . ' - ' . $this->translation[$box['name']];
				}
			}
		}
		
		$boxesAssoc = array(
			0 => $this->translation['Box is not linked.']
		) + $boxesAssoc;
		
		foreach($boxes as $name => $active){
			$form->addSelect($name, $name, $boxesAssoc)
					->setTranslator(NULL)
					->setAttribute('class', 'form-control');
		}
		
		// set defaults
		$boxes = $this->em->getRepository('AdminModule\Box')->findBy(array(
			'pageTo' => $this->actualPage
		)); 
		
		$defaults = array();
		foreach($boxes as $box){
			$defaults[$box->getBox()] = $box->getPageFrom()->getId() . '-' . $box->getPresenter() . '-' . $box->getFunction();
		}
		
		$form->setDefaults($defaults);
		$form->addSubmit('submit', 'Save');
		$form->onSuccess[] = callback($this, 'boxesFormSubmitted');
		
		return $form;
	}
	
	public function boxesFormSubmitted(UI\Form $form){
		$values = $form->getValues();
		
		// delete old asscociations
		$q = $this->em->createQuery('delete from AdminModule\Box m where m.pageTo = ' . $this->actualPage->getId());
		$numDeleted = $q->execute();
		
		// persist new associations
		foreach($values as $key => $value){
			if($value){
				$params = explode('-', $value);

				$pageFrom = $this->em->find('AdminModule\Page', $params[0]);

				$box = new Box();
				$box->setPageFrom($pageFrom);
				$box->setPageTo($this->actualPage);
				$box->setPresenter($params[1]);
				$box->setFunction($params[2]);
				$box->setBox($key);

				$this->em->persist($box);
			}
		}
		
		$this->em->flush();
		
		$this->flashMessage($this->translation['Boxes settings has been saved.'], 'success');
		if(!$this->isAjax())
			$this->redirect('this');
	}
}