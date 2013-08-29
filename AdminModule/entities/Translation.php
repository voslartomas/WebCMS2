<?php

namespace AdminModule;

use Doctrine\ORM\Mapping as orm;

/**
 * Entity class Translation.
 * @orm\Entity
 * @author Tomáš Voslař <t.voslar at google.com>
 */
class Translation extends \AdminModule\Doctrine\Entity {
	/**
	 * @orm\Column
	 * @var String 
	 */
	private $key;
	/**
	 * @orm\Column(type="text")
	 * @var String 
	 */
	private $translation;
	/**
	 * @orm\OneToOne(targetEntity="Language")
	 * @var Int 
	 */
	private $language;
	/**
	 * @orm\Column(type="boolean")
	 * @var Boolean 
	 */
	private $backend;
	
	public function getKey() {
		return $this->key;
	}

	public function getTranslation() {
		return $this->translation;
	}

	public function getLanguage() {
		return $this->language;
	}

	public function getBackend() {
		return $this->backend;
	}

	public function setKey(String $key) {
		$this->key = $key;
	}

	public function setTranslation(String $translation) {
		$this->translation = $translation;
	}

	public function setLanguage(Int $language) {
		$this->language = $language;
	}

	public function setBackend(Boolean $backend) {
		$this->backend = $backend;
	}
}