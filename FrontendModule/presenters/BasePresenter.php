<?php

namespace FrontendModule;

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
	public $language;
	
	/* @var User */
	public $systemUser;
	
	/* @var \WebCMS\Settings */
	public $settings;
	
	/* @var Page */
	public $actualPage;
	
	/* @var string */
	public $abbr;
	
	/* @var Array */
	public $languages;
	
	/* Method is executed before render. */
	protected function beforeRender(){
		
		$this->setLayout("layout");
		
		if($this->isAjax()){
			$this->invalidateControl('flashMessages');
		}
		
		$this->template->registerHelperLoader('\WebCMS\SystemHelper::loader');
		
		// get top page for sidebar menu
		$top = $this->actualPage;
		while($top->getParent() != NULL && $top->getLevel() > 1){
			$top = $top->getParent();
		}
		
		$this->template->structures = $this->getStructures(FALSE, 'nav navbar-nav', TRUE);
		$this->template->sidebar = $this->getStructure($top, FALSE, 'nav');
		
		$this->template->setTranslator($this->translator);
		$this->template->actualPage = $this->actualPage;
		$this->template->user = $this->getUser();
		$this->template->activePresenter = $this->getPresenter()->getName();
		$this->template->languages = $this->em->getRepository('AdminModule\Language')->findAll();
	}
	
	/* Startup method. */
	protected function startup(){
		parent::startup();
		
		// set language
		if(is_numeric($this->getParam('language'))) $this->language = $this->em->find('AdminModule\Language', $this->getParam('language'));
		else $this->language = $this->em->find('AdminModule\Language', 1); // TODO default language for frontend
		
		$this->abbr = $this->language->getDefaultFrontend() ? '' : $this->language->getAbbr() . '/';
		
		// load languages
		$this->languages = $this->em->getRepository('AdminModule\Language')->findAll();
		
		// translations
		$translation = new \WebCMS\Translation($this->em, $this->language , 0);
		$this->translation = $translation->getTranslations();
		$this->translator = new \WebCMS\Translator($this->translation);
		
		$id = $this->getParam('id');
		if($id) $this->actualPage = $this->em->find('AdminModule\Page', $id);
	}
	
	public function createForm(){
		$form = new UI\Form();
		
		$form->setTranslator($this->translator);
		
		return $form;
	}
	
	public function createComponentLanguagesForm(){
		$form = $this->createForm();
		$form->getElementPrototype()->action = $this->link('this', array(
			'id' => $this->actualPage->getId(),
			'path' => $this->actualPage->getPath(),
			'abbr' => $this->abbr,
			'do' => 'languagesForm-submit'
		));
		
		
		$items = array();
		foreach($this->languages as $lang){
			$items[$lang->getId()] = $lang->getName(); 	
		}
		
		$form->addSelect('language', 'Change language')->setItems($items)->setDefaultValue($this->language->getId());
		$form->addSubmit('submit', 'Change');
		$form->onSuccess[] = callback($this, 'languagesFormSubmitted', array('abbr' => '', 'path' => $this->actualPage->getPath()));
		
		
		
		return $form;
	}
	
	public function languagesFormSubmitted($form){
		$values = $form->getValues();
		
		$home = $this->em->getRepository('AdminModule\Page')->findOneBy(array(
			'language' => $values->language,
			'default' => TRUE
		));
		
		if(is_object($home)){
		
			$abbr = $home->getLanguage()->getDefaultFrontend() ? '' : $home->getLanguage()->getAbbr() . '/';

			$this->redirectUrl($this->link('this', array(
				'id' => $home->getId(),
				'path' => $home->getPath(),
				'abbr' => $abbr,
			)));
		}else{
			$this->flashMessage($this->translation['No default page for selected language.'], 'error');
		}
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
	 * 
	 * @return type
	 */
	private function getStructures($direct = TRUE, $rootClass = 'nav navbar-nav', $dropDown = FALSE){
		$repo = $this->em->getRepository('AdminModule\Page');
		
		$structs = $repo->findBy(array(
			'language' => $this->language,
			'parent' => NULL
		));
		
		$structures = array();
		foreach($structs as $s){
			$structures[$s->getTitle()] = $this->getStructure($s, $direct, $rootClass, $dropDown);
		}
		
		return $structures;
	}
	
	private function getStructure($node = NULL, $direct = TRUE, $rootClass = 'nav navbar-nav', $dropDown = FALSE){
		$repo = $this->em->getRepository('AdminModule\Page');
		
		return $repo->childrenHierarchy($node, $direct, array(
							'decorate' => true,
							'html' => true,
							'rootOpen' => function($nodes) use($rootClass, $dropDown){
								
								$drop = $nodes[0]['level'] == 2 ? TRUE : FALSE;
								$class = $nodes[0]['level'] < 2 ? $rootClass : '';
								
								if($drop && $dropDown)
									$class .= ' dropdown-menu';
								
								return '<ul class="' . $class . '">';
							},
							'rootClose' => '</ul>',
							'childOpen' => function($node) use($dropDown){
								$hasChildrens = count($node['__children']) > 0 ? TRUE : FALSE;
								$active = $this->getParam('id') == $node['id'] ? TRUE : FALSE;
								$class = '';
								
								if($this->getParam('lft') > $node['lft'] && $this->getParam('lft') < $node['rgt'] && $this->getParam('root') == $node['root']){
									$class .= ' active';
								}
								
								
								if($hasChildrens && $dropDown)
									$class .= ' dropdown';
								
								if($active)
									$class .= ' active';
								
								return '<li class="' . $class . '">';
							},
							'childClose' => '</li>',
							'nodeDecorator' => function($node) use($dropDown) {
								$hasChildrens = count($node['__children']) > 0 ? TRUE : FALSE;
								$params = '';
								$class = '';
								$link = $this->link(':Frontend:' . $node['moduleName'] . ':' . $node['presenter'] . ':default', array('id' => $node['id'], 'path' => $node['path'], 'abbr' => $this->abbr));
								
								if($hasChildrens && $node['level'] == 1 && $dropDown){
									$params = ' data-toggle="dropdown"';
									$class .= ' dropdown-toggle';
									$link = '#';
								}
								
								return '<a ' . $params .' class="' . $class . '" href="' . $link . '">'.$node['title'].'</a>';
							}
						));
	}
}