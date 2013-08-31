<?php 

namespace WebCMS;

use AdminModule, Nette;

/**
 * 
 */
class Translation  extends \ArrayObject {
	
	private $translations;
	private $em;
	private $language;
	private $backend;
	
	/**
	 * A constructor
	 * Prevents direct creation of object
	 */
	public function __construct($em, $language, $backend){
		
		$this->translations = $em->getRepository('AdminModule\Translation')->findBy(array(
			'language' => $language,
			'backend' => $backend
		));
		
		$this->em = $em;
		$this->language = $language;
		$this->backend = $backend;
	}

	public function getTranslations(){

		$translation = new TranslationArray($this);

		foreach($this->translations as $t){
			$translation[$t->getKey()] = $t->getTranslation();
		}

		return $translation;
	}

	public function getTranslationByKey($key){

		$translations = $this->getTranslations()->getData();

		foreach($translations as $k => $value){
			if($k == $key) return $value;
		}

		// save translation if not exists
		$this->addTranslation($key, $key);
	}

	public function addTranslation($key, $value = ""){
		$translation = new AdminModule\Translation();
		
		$translation->setKey($key);
		$translation->setTranslation($value);
		$translation->setLanguage($this->language);
		$translation->setBackend($this->backend);

		$this->em->persist($translation);
		$this->em->flush();
	}


}