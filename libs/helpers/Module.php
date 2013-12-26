<?php

namespace WebCMS;

/**
 * Description of Module
 *
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
abstract class Module implements IModule {
	
	protected $name;
	
	protected $author;
	
	protected $presenters;

	protected $boxes = array();
	
	protected $cloneable = false;
	
	protected $translatable = false;
	
	public function getName() {
		return $this->name;
	}

	public function setName($name) {
		$this->name = $name;
	}

	public function getAuthor() {
		return $this->author;
	}

	public function setAuthor($author) {
		$this->author = $author;
	}

	public function getPresenters() {
		return $this->presenters;
	}

	public function setPresenters($presenters) {
		$this->presenters = $presenters;
	}
	
	/**
	 * 
	 * @param string $name
	 * @param string $presenter
	 * @param string $function
	 */
	public function addBox($name, $presenter, $function, $moduleName = NULL){
		
		if(!$moduleName)
			$moduleName = $presenter;
		
		$this->boxes[] = array(
			'key' => \Nette\Utils\Strings::webalize($name),
			'name' => $name,
			'presenter' => $presenter,
			'module' => $moduleName,
			'function' => $function
		);
	}
	
	public function getBoxes(){
		return $this->boxes;
	}
	
	public function getPresenterSettings($presenter){
		$presenters = $this->getPresenters();
		
		foreach($presenters as $p){
			if($p['name'] === $presenter)
				return $p;
		}
		
		return FALSE;
	}
	
	public function cloneData($entityManager, $oldLanguge, $newLanguage, $transformTable){
		if(!$this->isCloneable()){
			return false;
		}
	}
	
	public function isCloneable(){
		return $this->cloneable;
	}
	
	public function translateData($entityManager, $language, $from, $to, \Webcook\Translator\ITranslator $translator){
		if(!$this->isTranslatable()){
			return false;
		}
	}
	
	public function isTranslatable(){
		return $this->translatable;
	}
}