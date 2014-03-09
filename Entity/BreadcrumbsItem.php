<?php

namespace WebCMS\Entity;

/**
 *
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class BreadcrumbsItem {

    private $id;
    private $moduleName;
    private $presenter;
    private $title;
    private $path;

    function __construct($id, $moduleName, $presenter, $title, $path) {
	$this->id = $id;
	$this->moduleName = $moduleName;
	$this->presenter = $presenter;
	$this->title = $title;
	$this->path = $path;
    }

    public function getId() {
	return $this->id;
    }

    public function setId($id) {
	$this->id = $id;
    }

    public function getModuleName() {
	return $this->moduleName;
    }

    public function getPresenter() {
	return $this->presenter;
    }

    public function getTitle() {
	return $this->title;
    }

    public function getPath() {
	return $this->path;
    }

    public function setModuleName($moduleName) {
	$this->moduleName = $moduleName;
    }

    public function setPresenter($presenter) {
	$this->presenter = $presenter;
    }

    public function setTitle($title) {
	$this->title = $title;
    }

    public function setPath($path) {
	$this->path = $path;
    }

}
