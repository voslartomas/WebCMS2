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
	
	public function __construct($em){
		$this->em = $em;
	}
	
    function match(\Nette\Http\IRequest $httpRequest){ 
		
		$path = $httpRequest->getUrl()->getPath();
		$query = str_replace($httpRequest->getUrl()->getScriptPath(), '', $path);
		
		$path = explode('/', $query);
		
		$paramCount = count($path);
		$pages = array();

		// look for matching parameter
		while(count($pages) == 0 && $paramCount != -1){
			// checks whether page exists
			$paramCount--;
			if($paramCount > -1) $lastParam = $path[$paramCount];
			
			$pageRepo = $this->em->getRepository('AdminModule\Page');
			$pages = $pageRepo->findBy(array(
				'slug' => $lastParam
			));

			if($paramCount >= 0){
				$lastParam = $path[$paramCount];
				
			}
		}		
		// setting of parameters and path
		$params = count($path) - ($paramCount + 1);

		if($params > 0){
			$parameters = array_slice($path, -$params);
			if(empty($parameters[count($parameters) - 1])) unset($parameters[count($parameters) - 1]);
		}else{
			$parameters = array();
		}

		$path = array_slice($path, 0, count($path) - $params);

		// takes the right one
		$page = NULL;
		foreach($pages as $p){
			$page = $p;
			
			// abbreviation of language
			$abbr = $page->getLanguage()->getDefaultFrontend() ? '' : $page->getLanguage()->getAbbr() . '/';
			
			$paths = $pageRepo->getPath($p);
			
			// check path
			$finalPath = '';
			foreach($paths as $pat){
				if($pat->getParent() != NULL) $finalPath .= $pat->getSlug() . '/';
			}
			
			$finalPath = $abbr . $finalPath;
			
			$moduleObject = $this->createObject($page->getModule()->getName());
			$presenterSettings = $moduleObject->getPresenterSettings($page->getPresenter());
			
			if(implode('/', $path) == substr($finalPath, 0, -1) && (count($parameters) === 0 || $presenterSettings['parameters'])){
				$this->page = $page;
				
				$path = $page->getPath();
				
				$params = array(
					'id' => $page->getId(),
					'language' => $this->page->getLanguage()->getId(),
					'path' => $path,
					'parameters' => $parameters,
					'root' => $page->getRoot(),
					'lft' => $page->getLeft(),
					'abbr' => $abbr) + $httpRequest->getQuery();
				
				$presenter = 'Frontend:' . $page->getModule()->getName() . ':' . $page->getPresenter();
				return new \Nette\Application\Request(
						$presenter,
						$httpRequest->getMethod(),
						$params,
						$httpRequest->getPost(),
						$httpRequest->getFiles(),
						array(\Nette\Application\Request::SECURED => $httpRequest->isSecured())
					);
			}
		}
		
		return NULL;
	}

    function constructUrl(\Nette\Application\Request $appRequest, \Nette\Http\Url $refUrl) {
		$params = $appRequest->getParameters();
		
		if(array_key_exists('abbr', $params)) $abbr = $params['abbr'];
		else $abbr = '';
		
		if(array_key_exists('path', $params)) $path = $params['path'];
		else $path = '';
		
		if(array_key_exists('do', $params)) $do = '?do=' . $params['do'];
		else $do = '';
		
		if(array_key_exists('parameters', $params)){
			if(count($params['parameters']) > 0){
				$path .= '/' . implode('/', $params['parameters']);
			}
		}
		
		return $refUrl->getScheme() . '://' . $refUrl->getHost() . $refUrl->getPath() . $abbr . $path . $do;
	}
	
	private function createObject($name){
		$expl = explode('-', $name);

		$objectName = ucfirst($expl[0]);
		$objectName = "\\WebCMS\\$objectName" . "Module\\" . $objectName;
		
		return new $objectName;
	}
}

