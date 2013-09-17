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
	 * @var type 
	 */
	private $name;
	
	/**
	 * @ORM\Column(type="text")
	 * @var type 
	 */
	private $value;
	
	/**
	 * @ORM\Column
	 * @var type 
	 */
	private $section;
	
	/**
	 * @ORM\Column
	 * @var type 
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

	public function setName(type $name) {
		$this->name = $name;
	}

	public function getValue() {
		return $this->value;
	}

	public function setValue(type $value) {
		$this->value = $value;
	}

	public function getLanguage() {
		return $this->language;
	}

	public function setLanguage(Int $language) {
		$this->language = $language;
	}
	
	public function getType() {
		return $this->type;
	}

	public function setType(type $type) {
		$this->type = $type;
	}
}