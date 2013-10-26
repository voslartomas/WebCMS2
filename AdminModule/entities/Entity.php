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
	public function toArray($notEmptyValues = FALSE){
		$props = $this->getReflection()->getProperties();
		if($this->getReflection()->getParentClass()){
			$temp = $this->getReflection()->getParentClass()->getProperties();
			$props = array_merge($props, $temp);
		}

		if(strpos($this->getReflection()->getName(), '__CG__') !== FALSE) $props = $this->getReflection()->getParentClass()->getProperties();

		$array = array();
		foreach($props as $prop){
			$getter = 'get' . ucfirst($prop->getName());
			
			if(method_exists($this, $getter)){
				
				$empty = $this->$getter();
				$empty = is_null($empty) || empty($empty);
				
				if(!is_object($this->$getter())){
					if(($notEmptyValues && !$empty) || !$empty){
						$array[$prop->getName()] = $this->$getter();
					}
				}
				elseif(is_object($this->$getter())){
					if(method_exists($this->$getter(), 'getId')){
						$array[$prop->getName()] = $this->$getter()->getId();
					}else if($this->$getter() instanceof \DateTime){
						$array[$prop->getName()] = $this->$getter()->format('d.m.Y');
					}
				}
			}
		}
		
		return $array;
	}
}