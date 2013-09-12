<?php

/**
 * @license http://URL name
 */

namespace AdminModule;

use Doctrine\ORM\Mapping as orm;

/**
 * Basic SEO entity which contains basic SEO parameters.
 * @orm\mappedSuperclass
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
abstract class Seo extends Doctrine\Entity{
	/**
	 * @orm\Column(type="text", nullable=true)
	 */
	private $metaTitle;
	
	/**
	 * @orm\Column(type="text", nullable=true)
	 */
	private $metaDescription;
	
	/**
	 * @orm\Column(type="text", nullable=true)
	 */
	private $metaKeywords;
	
	/* TODO */
	private $url;
	
	public function getMetaTitle() {
		return $this->metaTitle;
	}

	public function setMetaTitle($metaTitle) {
		$this->metaTitle = $metaTitle;
	}

	public function getMetaDescription() {
		return $this->metaDescription;
	}

	public function setMetaDescription($metaDescription) {
		$this->metaDescription = $metaDescription;
	}

	public function getMetaKeywords() {
		return $this->metaKeywords;
	}

	public function setMetaKeywords($metaKeywords) {
		$this->metaKeywords = $metaKeywords;
	}
}