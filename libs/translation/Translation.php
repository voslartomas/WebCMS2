<?php 

namespace WebCMS;

use AdminModule, Nette;

/**
 * 
 */
class Translation  extends \ArrayObject {
	
	private $translations = null;
	private $em;
	private $language;
	private $backend;
	
	const CACHE_NAMESPACE = 'frontendTranslations';
	
	/**
	 * A constructor
	 * Prevents direct creation of object
	 */
	public function __construct($em, $language, $backend, $cacheStorage = null){
	
	    $this->translations = new TranslationArray($this);
	    
	    // cache translations for frontend
	    if($cacheStorage != null && $backend == false){
		$cache = new \Nette\Caching\Cache($cacheStorage);

		if(!$translations = $cache->load(self::CACHE_NAMESPACE)){

		    $translations = $this->loadFromDb($em, $language, $backend);

		    foreach($translations as $t){
			    $this->translations[$t->getKey()] = $t->getTranslation();
		    }

		    $cache->save(self::CACHE_NAMESPACE, $this->translations->getData(), array(
				    \Nette\Caching\Cache::TAGS => array(self::CACHE_NAMESPACE),
				));
		}else{
		   
		   $this->translations->setData($translations);
		}
	    }else{
		$translations = $this->loadFromDb($em, $language, $backend);
		
		foreach($translations as $t){
			$this->translations[$t->getKey()] = $t->getTranslation();
		}
	    }
		
	    $this->em = $em;
	    $this->language = $language;
	    $this->backend = $backend;
	}
	
	private function loadFromDb($em, $language, $backend){
	    return $em->getRepository('AdminModule\Translation')->findBy(array(
			    'language' => $language,
			    'backend' => $backend
		    ));
	}
	
	public function getTranslations(){
		return $this->translations;
	}

	public function getTranslationByKey($key){

		$translations = $this->getTranslations()->getData();

		foreach($translations as $k => $value){
			if($k == $key) return $value;
		}

		// save translation if not exists
		$this->addTranslation($key, $key);
		
		return $key;
	}

	public function addTranslation($key, $value = ""){
		$translation = new AdminModule\Translation();
		if($key){
			$translation->setKey($key);
			$translation->setTranslation($value);
			$translation->setLanguage($this->language);
			$translation->setBackend($this->backend);

			$this->em->persist($translation);
			$this->em->flush();
		}
	}


}