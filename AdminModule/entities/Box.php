<?php

namespace AdminModule;

use Doctrine\ORM\Mapping as orm;

/**
 * Description of Box
 * @orm\Entity
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class Box extends \AdminModule\Doctrine\Entity{
	
	/**
	 * @orm\ManyToOne(targetEntity="Page")
	 * @orm\JoinColumn(name="page_from_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	private $pageFrom;
	
	/**
	 * @orm\ManyToOne(targetEntity="Page")
	 * @orm\JoinColumn(name="page_to_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	private $pageTo;
	
	/**
	 * @orm\Column
	 */
	private $box;
	
	/**
	 * @orm\Column
	 */
	private $presenter;
	
	/**
	 * @orm\Column
	 */
	private $moduleName;
	
	/**
	 * @orm\Column
	 */
	private $function;
	
	public function getPageFrom() {
		return $this->pageFrom;
	}

	public function setPageFrom($pageFrom) {
		$this->pageFrom = $pageFrom;
	}

	public function getPageTo() {
		return $this->pageTo;
	}

	public function setPageTo($pageTo) {
		$this->pageTo = $pageTo;
	}

	public function getBox() {
		return $this->box;
	}

	public function setBox($box) {
		$this->box = $box;
	}
	
	public function getPresenter() {
		return $this->presenter;
	}

	public function setPresenter($presenter) {
		$this->presenter = $presenter;
	}
	
	public function getFunction() {
		return $this->function;
	}

	public function setFunction($function) {
		$this->function = $function;
	}
	
	public function getModuleName() {
		return $this->moduleName;
	}

	public function setModuleName($moduleName) {
		$this->moduleName = $moduleName;
	}
}

?>
