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
	 * @var Booleand 
	 */
	private $default;
	
	public function getDefault() {
		return $this->default;
	}

	public function setDefault(Booleand $default) {
		$this->default = $default;
	}
	
	public function getName() {
		return $this->name;
	}

	public function setName(String $name) {
		$this->name = $name;
		return $this;
	}

	public function getAbbr() {
		return $this->abbr;
	}

	public function setAbbr(String $abbr) {
		$this->abbr = $abbr;
		return $this;
	}

}
