<?php

namespace FrontendModule;

/**
 * Admin presenter.
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 * @package WebCMS2
 */
class HomepagePresenter extends \FrontendModule\BasePresenter{

	protected function beforeRender(){
		parent::beforeRender();
	}
	
	protected function startup(){		
		parent::startup();
		
		$page = $this->em->getRepository('WebCMS\Entity\Page')->findOneBy(array(
			'default' => 1,
			'language' => $this->language
		));
		
		$abbr = $page->getLanguage()->getDefaultFrontend() ? '' : $page->getLanguage()->getAbbr() . '/';
		
		$this->redirect(':Frontend:' . $page->getModuleName() . ':' . $page->getPresenter() . ':default', array('id' => $page->getId(), 'path' => $page->getPath(), 'abbr' => $abbr));
	}
}