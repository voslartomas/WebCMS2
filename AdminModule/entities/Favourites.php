<?php

namespace AdminModule;

use Doctrine\ORM\Mapping as orm;

/**
 * Description of favourites
 * @orm\Entity
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class Favourites extends \AdminModule\Doctrine\Entity{
	
	/**
	 * @orm\Column
	 */
	private $link;
	
	/**
	 * @orm\ManyToOne(targetEntity="User")
	 * @orm\JoinColumn(onDelete="CASCADE")
	 * @var User 
	 */
	private $user;
	
	/**
	 * @orm\Column
	 */
	private $title;
	
	public function getLink() {
		return $this->link;
	}

	public function getUser() {
		return $this->user;
	}

	public function setLink($link) {
		$this->link = $link;
	}

	public function setUser($user) {
		$this->user = $user;
	}
	
	public function getTitle() {
		return $this->title;
	}

	public function setTitle($title) {
		$this->title = $title;
	}
}