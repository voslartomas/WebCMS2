<?php

namespace WebCMS\AdminModule\Doctrine;

use Doctrine\ORM\Mapping as orm;

/**
 * Description of Page
 * @orm\Entity
 * @author Tomáš Voslař <t.voslar at gmail.com>
 */
class Page extends \WebCMS\Doctrine\Entity{
	/**
	 * @orm\Column(type="integer")
	 * @var Int 
	 */
	private $left;
	/**
	 * @orm\Column(type="integer")
	 * @var Int 
	 */
	private $right;
	/**
	 * @orm\Column(type="integer")
	 * @var Int 
	 */
	private $level;
	/**
	 * @orm\Column
	 * @var String 
	 */
	private $title;
	/**
	 * @orm\Column(type="integer")
	 * @var Int 
	 */
	private $idLanguage;
	
	public function getLeft() {
		return $this->left;
	}

	public function setLeft(Int $left) {
		$this->left = $left;
		return $this;
	}

	public function getRight() {
		return $this->right;
	}

	public function setRight(Int $right) {
		$this->right = $right;
		return $this;
	}

	public function getLevel() {
		return $this->level;
	}

	public function setLevel(Int $level) {
		$this->level = $level;
		return $this;
	}

	public function getTitle() {
		return $this->title;
	}

	public function setTitle(String $title) {
		$this->title = $title;
		return $this;
	}
	
	public function getIdLanguage() {
		return $this->idLanguage;
	}

	public function setIdLanguage(Int $idLanguage) {
		$this->idLanguage = $idLanguage;
		return $this;
	}
}
