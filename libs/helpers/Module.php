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
	public function addBox($name, $presenter, $function){
		
		$this->boxes[] = array(
			'key' => \Nette\Utils\Strings::webalize($name),
			'name' => $name,
			'presenter' => $presenter,
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
}