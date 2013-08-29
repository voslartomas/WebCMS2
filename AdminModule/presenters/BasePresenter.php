<?php

namespace AdminModule;

use Nette;

/**
 * Base class for all application presenters.
 *
 * @author     Tomáš Voslař <tomas.voslar at webcook.cz>
 * @package    WebCMS2
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter{
	/** @var Doctrine\ORM\EntityManager */
	protected $em;
	
	/* Method is executed before render. */
	protected function beforeRender(){
		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect('Login:');
		}
		
		$this->setLayout("layout");
		
		$this->template->version = \WebCMS\SystemHelper::getVersion();
	}
	
	/* Startup method. */
	protected function startup(){
		parent::startup();
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
