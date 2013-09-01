<?php

namespace AdminModule;

use Doctrine\ORM\Mapping as orm;

/**
 * Description of Language entity.
 * @orm\Entity
 * @author Tomáš Voslař <t.voslar at gmail.com>
 */
class Language extends \AdminModule\Doctrine\Entity{
	/**
	 * @orm\Column
	 * @var String 
	 */
	private $name;
	/**
	 * @orm\Column
	 * @var String 
	 */
	private $abbr;
	/**
	 * @orm\Column(type="boolean")
	 * @var Boolean 
	 */
	private $defaultFrontend;
	/**
	 * @orm\Column(type="boolean")
	 * @var Boolean 
	 */
	private $defaultBackend;
	/**
	 * @orm\OneToMany(targetEntity="Translation", mappedBy="language") 
	 * @var Array
	 */
	private $translations;
	
	public function getTranslations() {
		return $this->translations;
	}

	public function setTranslations(Array $translations) {
		$this->translations = $translations;
	}
	
	public function getDefaultBackend() {
		return $this->defaultBackend;
	}

	public function setDefaultBackend($defaultBackend) {
		$this->defaultBackend = $defaultBackend;
	}
	
	public function getDefaultFrontend() {
		return $this->defaultFrontend;
	}

	public function setDefaultFrontend($defaultFrontend) {
		$this->defaultFrontend = $defaultFrontend;
	}
	
	public function getName() {
		return $this->name;
	}

	public function setName($name) {
		$this->name = $name;
		return $this;
	}

	public function getAbbr() {
		return $this->abbr;
	}

	public function setAbbr($abbr) {
		$this->abbr = $abbr;
		return $this;
	}

}
