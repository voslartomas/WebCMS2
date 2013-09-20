<?php

namespace WebCMS;

/**
 * Description of Module
 *
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
abstract class Module implements IModule {
	
	protected $name;
	
	protected $version;
	
	protected $author;
	
	protected $presenters;

	public function getName() {
		return $this->name;
	}

	public function setName($name) {
		$this->name = $name;
	}

	public function getVersion() {
		return $this->version;
	}

	public function setVersion($version) {
		$this->version = $version;
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


}