<?php

namespace AdminModule;

use Nette;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use Nette\Application\UI;

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
	
	/* @var \WebCMS\Translator */
	public $translator;
	
	/* @var Nette\Http\SessionSection */
	public $state;
	
	/* Method is executed before render. */
	protected function beforeRender(){
		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect('Login:');
		}
		
		$this->setLayout("layout");
		
		if($this->isAjax()){
			$this->invalidateControl('flashMessages');
		}
		
		$this->template->registerHelperLoader('\WebCMS\SystemHelper::loader');
		
		$this->template->setTranslator($this->translator);
		$this->template->language = $this->state->language;
		$this->template->version = \WebCMS\SystemHelper::getVersion();
		$this->template->activePresenter = $this->getPresenter()->getName();
		$this->template->languages = $this->em->getRepository('AdminModule\Language')->findAll();
	}
	
	/* Startup method. */
	protected function startup(){
		parent::startup();
		
		$this->state = $this->getSession('admin');

		// changing language
		if($this->getParameter('language_id_change')){
			$this->state->language = $this->em->find('AdminModule\Language', $this->getParameter('language_id_change'));
			$this->redirect('Homepage:default');
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
		
		$translation = new \WebCMS\Translation($this->em, $language , 1);
		$this->translation = $translation->getTranslations();
		$this->translator = new \WebCMS\Translator($this->translation);
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
}