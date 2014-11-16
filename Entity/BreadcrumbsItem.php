<?php

namespace WebCMS\Entity;

/**
 *
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class BreadcrumbsItem
{
    private $id;
    private $moduleName;
    private $presenter;
    private $title;
    private $path;

    public function __construct($id, $moduleName, $presenter, $title, $path)
    {
        $this->id = $id;
        $this->moduleName = $moduleName;
        $this->presenter = $presenter;
        $this->title = $title;
        $this->path = $path;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    public function getModuleName()
    {
        return $this->moduleName;
    }

    public function getPresenter()
    {
        return $this->presenter;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $moduleName
     */
    public function setModuleName($moduleName)
    {
        $this->moduleName = $moduleName;
    }

    /**
     * @param string $presenter
     */
    public function setPresenter($presenter)
    {
        $this->presenter = $presenter;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }
}
