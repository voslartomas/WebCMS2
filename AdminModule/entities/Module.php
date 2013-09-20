<?php

namespace AdminModule;

use Doctrine\ORM\Mapping as orm;

/**
 * Description of Module entity.
 * @orm\Entity
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class Module extends \AdminModule\Doctrine\Entity{
	/**
	 * @orm\Column
	 */
	private $name;
	
	/**
	 * @orm\Column 
	 */
	private $presenters;
	
	/**
	 * @orm\Column(type="boolean")
	 */
	private $active;

	public function getName() {
		return $this->name;
	}

	public function setName($name) {
		$this->name = $name;
	}

	public function getActive() {
		return $this->active;
	}

	public function setActive($active) {
		$this->active = $active;
	}
	
	public function getPresenters() {
		return unserialize($this->presenters);
	}

	public function setPresenters($presenters) {
		$this->presenters = serialize($presenters);
	}
}
