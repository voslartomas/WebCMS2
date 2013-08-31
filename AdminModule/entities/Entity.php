<?php

/**
 * @license http://URL name
 */

namespace AdminModule\Doctrine;

use Doctrine\ORM\Mapping as orm;

/**
 * Basic entity with identificator.
 * @orm\mappedSuperclass
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 * @property-read int $id
 */
abstract class Entity extends \Nette\Object{
	/**
	 * @orm\id
	 * @orm\generatedValue
	 * @orm\column(type="integer")
	 * @var int
	 */
	public $id;

	/**
	 * @return int
	 */
	public function getId(){
		return $this->id;
	}
	
	/**
	 * Converts object into array.
	 * @return Array
	 */
	public function toArray(){
		$props = $this->getReflection()->getProperties();
		
		$array = array();
		foreach($props as $prop){
			$getter = 'get' . ucfirst($prop->getName());
			
			if(method_exists($this, $getter))
					$array[$prop->getName()] = $this->$getter();
		}
		
		return $array;
	}
}