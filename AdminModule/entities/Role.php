<?php

namespace AdminModule;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Role
 * @ORM\Entity
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class Role extends Doctrine\Entity{
	/**
	 * @ORM\Column(unique=true)
	 * @var string
	 */
	private $name;
	
	/**
	 * @orm\ManyToMany(targetEntity="Permission")
	 * @orm\JoinColumn(name="permission_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	private $permissions;
	
	public function getName() {
		return $this->name;
	}

	public function setName($name) {
		$this->name = $name;
	}
	
	public function getPermissions() {
		return $this->permissions;
	}

	public function setPermissions($permissions) {
		$this->permissions = $permissions;
	}
}

