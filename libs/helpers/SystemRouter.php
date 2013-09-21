<?php

namespace WebCMS;

/**
 * Description of SystemRouter
 *
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class SystemRouter extends \Nette\Application\Routers\Route{
	
	/* @var \Doctrine\ORM\EntityManager */
	private $em;
	
	/* @var \AdminModule\Language */
	private $language;
	
	/* @var \AdminModule\Page */
	private $page;
	
	private $pages;
	
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
		$pageRepo = $this->em->getRepository('AdminModule\Page');
		$pages = $pageRepo->findBy(array(
			'slug' => $lastParam
		));
		
		// takes the right one
		$page = NULL;
		foreach($pages as $p){
			$page = $p;
			
			$paths = $pageRepo->getPath($p);
			
			// check path
			$finalPath = '';
			foreach($paths as $pat){
				if($pat->getParent() != NULL) $finalPath .= $pat->getSlug() . '/';
			}

			if(implode('/', $path) == substr($finalPath, 0, -1)){
				$this->page = $page;
				
				$presenter = 'Frontend:' . $page->getModule()->getName() . ':' . $page->getPresenter();
				return new \Nette\Application\Request($presenter, 'GET', array('id' => $page->getId(), 'language' => 1, 'path' => $page->getPath()) + $httpRequest->getQuery());
			}
		}
		
		return NULL;
	}

    function constructUrl(\Nette\Application\Request $appRequest, \Nette\Http\Url $refUrl) {
		$params = $appRequest->getParameters();

		return $refUrl->getScheme() . '://' . $refUrl->getHost() . $refUrl->getPath() . $params['path'];
	}
	
	private function defineLanguage($url){
		
	}
}

