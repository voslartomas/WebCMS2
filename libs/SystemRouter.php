<?php

namespace WebCMS;

/**
 * TODO refactor
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class SystemRouter extends \Nette\Application\Routers\Route
{
    /* @var \Doctrine\ORM\EntityManager */
    private $em;

    /* @var \WebCMS\Entity\Language */
    private $language;

    /* @var \WebCMS\Entity\Page */
    private $page;

    public function __construct($em)
    {
        $this->em = $em;
    }

    public function match(\Nette\Http\IRequest $httpRequest)
    {
        $path = $httpRequest->getUrl()->getPath();

        if ($httpRequest->getUrl()->getScriptPath() !== '/')
            $query = str_replace($httpRequest->getUrl()->getScriptPath(), '', $path);
        else
            $query = substr($path, 1, strlen($path));

        $path = explode('/', $query);

        $paramCount = count($path);
        $pages = array();
        $reversePath = array_reverse($path);
        // look for matching parameter
        foreach ($reversePath as $lastParam) { 
            // checks whether page exists
            $pageRepo = $this->em->getRepository('WebCMS\Entity\Page');
            $tmp = $pageRepo->findBy(array(
            'slug' => $lastParam
            ));
	    if (!empty($tmp)) {
		$pages = array_merge($pages, $tmp);	
	    }
        }

        // takes the right one
        $page = NULL;
        foreach ($pages as $p) {
            $page = $p;

            // abbreviation of language
            $abbr = $page->getLanguage()->getDefaultFrontend() ? '' : $page->getLanguage()->getAbbr() . '/';

            $paths = $pageRepo->getPath($p);

            // check path
            $finalPath = '';
            foreach ($paths as $pat) {
            if ($pat->getParent() != NULL)
                $finalPath .= $pat->getSlug() . '/';
            }

            $finalPath = $abbr . $finalPath;

            $moduleObject = $this->createObject($page->getModule()->getName());
            $presenterSettings = $moduleObject->getPresenterSettings($page->getPresenter());

$pathI = implode('/', $path);
$parameters = strstr($pathI, $finalPath);
$parameters = explode('/', str_replace($finalPath, '',$parameters));

$pathWithoutParameters = str_replace(implode('/', $parameters), '', $pathI);
$finalPath = substr($finalPath, 0, -1);

$parameters = array_filter($parameters, function($var){
 if (!empty($var)) { 
return $var;
}
});

            if (($pathWithoutParameters == $finalPath || $finalPath . '/' == $pathWithoutParameters) && (count($parameters) === 0 || $presenterSettings['parameters'])) {
            $this->page = $page;

            $path = $page->getPath();

if(count($parameters) > 0) {
$fullPath = $path . '/' . implode('/', $parameters);
} else {
$fullPath = $path;
}

            $params = array(
                'id' => $page->getId(),
                'language' => $this->page->getLanguage()->getId(),
                'path' => $path,
                'fullPath' => $fullPath . '/',
                'parameters' => $parameters,
                'root' => $page->getRoot(),
                'lft' => $page->getLeft(),
                'abbr' => $abbr) + $httpRequest->getQuery();

            $presenter = 'Frontend:' . $page->getModule()->getName() . ':' . $page->getPresenter();

            return new \Nette\Application\Request(
                $presenter, $httpRequest->getMethod(), $params, $httpRequest->getPost(), $httpRequest->getFiles(), array(\Nette\Application\Request::SECURED => $httpRequest->isSecured())
            );
            }
        }

        return NULL;
    }

    public function constructUrl(\Nette\Application\Request $appRequest, \Nette\Http\Url $refUrl)
    {
        $params = $appRequest->getParameters();

        $sign = '?';

        if (array_key_exists('abbr', $params))
            $abbr = $params['abbr'];
        else
            $abbr = '';

        if (array_key_exists('path', $params))
            $path = $params['path'];
        else
            $path = '';

        if (array_key_exists('do', $params)) {
            $do = $sign . 'do=' . $params['do'];
            $sign = '&';
        } else
            $do = '';
            
    	//TODO refactor
        if (array_key_exists('utm_source', $params)) {
            $utm = $sign . 'utm_source=' . $params['utm_source'];
            $sign = '&';
            if(array_key_exists('utm_medium', $params)){
                $utm .= $sign . 'utm_medium=' . $params['utm_medium'];
            }
            if(array_key_exists('utm_campaign', $params)){
                $utm .= $sign . 'utm_campaign=' . $params['utm_campaign'];
            }
            if(array_key_exists('utm_term', $params)){
                $utm .= $sign . 'utm_term=' . $params['utm_term'];
            }
            if(array_key_exists('utm_content', $params)){
                $utm .= $sign . 'utm_content=' . $params['utm_content'];
            }
            
            $sign = '?';
        } else
            $utm = '';

        if (array_key_exists('action', $params)) {

            $action = $params['action'] != 'default' ? $action = $sign . 'action=' . $params['action'] : '';
        } else
            $action = '';

        if (array_key_exists('parameters', $params)) {
            if (count($params['parameters']) > 0) {
            $path .= '/' . implode('/', $params['parameters']);
            }
        }
        // TODO refactor
        unset($params['abbr']);
        unset($params['path']);
        unset($params['do']);
        unset($params['utm_medium']);
        unset($params['utm_source']);
        unset($params['utm_campaign']);
        unset($params['utm_term']);
        unset($params['utm_content']);
        unset($params['action']);
        unset($params['parameters']);
        unset($params['lft']);
        unset($params['root']);
        unset($params['id']);
        unset($params['language']);
        unset($params['fullPath']);

        $path = $refUrl->getScheme() . '://' . $refUrl->getHost() . $refUrl->getPath() . $abbr . $path . $do . $utm . $action;

        $query = '';
        $index = 0;
        foreach ($params as $key => $value) {
            $e = strpos($path, '?') === FALSE ? '?' : '&';
            $query .= $e . $key . '=' . $value;

            $index++;
        }

        return $path . $query;
    }

    private function createObject($name)
    {
        $expl = explode('-', $name);

        $objectName = ucfirst($expl[0]);
        $objectName = "\\WebCMS\\$objectName" . "Module\\" . $objectName;

        return new $objectName;
    }

}
