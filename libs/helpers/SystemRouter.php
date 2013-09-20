<?php

namespace WebCMS;

/**
 * Description of SystemRouter
 *
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class SystemRouter implements \Nette\Application\IRouter{
	
	/* @var \Doctrine\ORM\EntityManager */
	private $em;
	
	/* @var \AdminModule\Language */
	private $language;
	
	public function __construct($em){
		$this->em = $em;
	}
	
    function match(\Nette\Http\IRequest $httpRequest){ 
		
		$this->defineLanguage($httpRequest->getUrl());
		
		$path = $httpRequest->getUrl()->getPath();
		$query = str_replace($httpRequest->getUrl()->getScriptPath(), '', $path);
		
		$path = explode('/', $query);
		
		$lastParam = $path[count($path) - 1];
		
		// checks whether page exists
		$pages = $this->em->getRepository('AdminModule\Page')->findBy(array(
			'slug' => $lastParam
		));
		
		// takes the right one
		$page = NULL;
		foreach($pages as $p){
			$page = $p;
		}
		
		if(!is_object($page))
			return NULL;
		
		$presenter = 'Frontend:' . $page->getModule()->getName() . ':' . $page->getPresenter();
		
		return new \Nette\Application\Request($presenter, 'POST', array('id' => $page->getId()) + $httpRequest->getQuery());
	}

    function constructUrl(\Nette\Application\Request $appRequest, \Nette\Http\Url $refUrl) {
	
		return 'test';
	}
	
	private function defineLanguage($url){
		
	}
}

