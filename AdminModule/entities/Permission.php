<?php

namespace AdminModule;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Permission
 * @ORM\Entity
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class Permission extends Doctrine\Entity {
	/**
	 * @ORM\Column
	 * @var string
	 */
	private $resource;
	
	/**
	 * @ORM\Column(type="boolean")
	 * @var type 
	 */
	private $read;
	
	/**
	 * @ORM\Column(type="boolean")
	 * @var type 
	 */
	private $write;
	
	/**
	 * @ORM\Column(type="boolean")
	 * @var type 
	 */
	private $remove;
	
	public function getResource() {
		return $this->resource;
	}

	public function setResource($resource) {
		$this->resource = $resource;
	}

	public function getRead() {
		return $this->read;
	}

	public function setRead(type $read) {
		$this->read = $read;
	}

	public function getWrite() {
		return $this->write;
	}

	public function setWrite(type $write) {
		$this->write = $write;
	}

	public function getRemove() {
		return $this->remove;
	}

	public function setRemove(type $remove) {
		$this->remove = $remove;
	}
}