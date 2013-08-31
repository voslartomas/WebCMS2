<?php

namespace AdminModule;

use Nette;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;


/**
 * Base class for all application presenters.
 *
 * @author     Tomáš Voslař <tomas.voslar at webcook.cz>
 * @package    WebCMS2
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter{
	/** @var Doctrine\ORM\EntityManager */
	protected $em;
	
	/* @var \WebCMS\Translation */
	public $translation;
	
	/* Method is executed before render. */
	protected function beforeRender(){
		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect('Login:');
		}
		
		$this->setLayout("layout");
		
		if($this->isAjax()){
			$this->invalidateControl('flashMessages');
		}
		
		$language = $this->em->getRepository('AdminModule\Language')->findOneBy(array(
			'defaultBackend' => 1
			));
		
		$translation = new \WebCMS\Translation($this->em, $language, 1);
		
		$this->translation = $translation->getTranslations();
		$this->template->translation = $this->translation;
		$this->template->version = \WebCMS\SystemHelper::getVersion();
		$this->template->activePresenter = $this->getPresenter()->getName();
	}
	
	/* Startup method. */
	protected function startup(){
		parent::startup();
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
	public function createGrid(Nette\Application\UI\Presenter $presenter, $name, $entity){
		$grid = new \Grido\Grid($presenter, $name);
		
		$qb = $this->em->createQueryBuilder();
		
		$grid->setModel($qb->select('l')->from("AdminModule\\$entity", 'l'));
		$grid->setRememberState();
		
		return $grid;
	}
	
	/**
	 * Creates form and rewrite renderer for bootstrap.
	 * @return type
	 */
	public function createForm(){
		$form = new Nette\Application\UI\Form();
		
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
}
