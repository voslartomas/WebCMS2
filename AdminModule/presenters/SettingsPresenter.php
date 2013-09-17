<?php

namespace AdminModule;

/**
 * Settings presenter.
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 * @package WebCMS2
 */
class SettingsPresenter extends \AdminModule\BasePresenter{

	protected function beforeRender(){
		parent::beforeRender();
	}
	
	protected function startup(){		
		parent::startup();
	}
	
	/* BASIC */
	
	public function renderDefault(){
		$this->reloadContent();
		
		$this->template->settings = $this->em->getRepository('AdminModule\Setting')->findBy(array(
			'section' => 'basic'
		));
	}
	
	/* PICTURES */
	
	public function renderPictures(){
		$this->reloadContent();
		
		$this->template->settings = $this->em->getRepository('AdminModule\Setting')->findBy(array(
			'section' => 'pictures'
		));
	}
	
	/* EMAILS */
	
	public function renderEmails(){
		$this->reloadContent();
		
		$this->template->settings = $this->em->getRepository('AdminModule\Setting')->findBy(array(
			'section' => 'emails'
		));
	}
}