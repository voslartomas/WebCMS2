<?php

namespace AdminModule;

use Doctrine\ORM\Mapping as orm;

/**
 * Description of Settings
 * @ORM\Entity
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class Setting extends Doctrine\Entity {
	/**
	 * @ORM\Column
	 * @var String 
	 */
	private $name;
	
	/**
	 * @ORM\Column(type="text")
	 * @var String 
	 */
	private $value;
	
	/**
	 * @ORM\Column
	 * @var String 
	 */
	private $section;
	
	/**
	 * @ORM\Column
	 * @var String 
	 */
	private $type;
	
	/**
	 * @orm\ManyToOne(targetEntity="Language")
	 * @orm\JoinColumn(name="language_id", referencedColumnName="id", onDelete="CASCADE")
	 * @var Int 
	 */
	private $language;
	
	public function getName() {
		return $this->name;
	}

	public function setName($name) {
		$this->name = $name;
	}

	public function getValue() {
		return $this->value;
	}

	public function setValue($value) {
		$this->value = $value;
	}

	public function getLanguage() {
		return $this->language;
	}

	public function setLanguage($language) {
		$this->language = $language;
	}
	
	public function getType() {
		return $this->type;
	}

	public function setType($type) {
		$this->type = $type;
	}
}