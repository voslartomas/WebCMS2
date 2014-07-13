<?php

namespace WebCMS\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as gedmo;
use Doctrine\orm\Mapping as orm;

/**
 * @gedmo\Tree(type="nested")
 * @orm\Entity(repositoryClass="PageRepository")
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class Page extends Seo
{
    /**
     * @orm\Column(length=64)
     */
    private $title;

    /**
     * @gedmo\Slug(fields={"title"})
     * @orm\Column(length=64, unique=true)
     */
    private $slug;

    /**
     * @gedmo\TreeLeft
     * @orm\Column(type="integer")
     */
    private $lft;

    /**
     * @gedmo\TreeRight
     * @orm\Column(type="integer")
     */
    private $rgt;

    /**
     * @gedmo\TreeParent
     * @orm\ManyToOne(targetEntity="Page", inversedBy="children")
     * @orm\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $parent;

    /**
     * @gedmo\TreeRoot
     * @orm\Column(type="integer", nullable=true)
     */
    private $root;

    /**
     * @gedmo\TreeLevel
     * @orm\Column(name="lvl", type="integer")
     */
    private $level;

    /**
     * @orm\OneToMany(targetEntity="Page", mappedBy="parent")
     */
    private $children;

    /**
     * @gedmo\Timestampable(on="create")
     * @orm\Column(type="datetime")
     */
    private $created;

    /**
     * @gedmo\Timestampable(on="update")
     * @orm\Column(type="datetime")
     */
    private $updated;

    /**
     * @orm\ManyToOne(targetEntity="Language")
     * @orm\JoinColumn(name="language_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $language;

    /**
     * @orm\ManyToOne(targetEntity="Module")
     * @orm\JoinColumn(name="module_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $module;

    /**
     * @orm\Column(nullable=true)
     */
    private $moduleName;

    /**
     * @orm\Column
     */
    private $presenter;

    /**
     * @orm\Column
     */
    private $path;

    /**
     * @orm\Column(type="boolean")
     */
    public $visible;

    /**
     * @orm\Column(type="boolean", name="`default`")
     */
    private $default;

    /**
     * @orm\Column
     */
    private $class;

    /**
     * @orm\OneToMany(targetEntity="Box", mappedBy="pageTo")
     */
    private $boxes;

    /**
     * @orm\Column(nullable=true)
     */
    private $redirect;

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function getRoot()
    {
        return $this->root;
    }

    public function getLevel()
    {
        return $this->level;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function getLeft()
    {
        return $this->lft;
    }

    public function getRight()
    {
        return $this->rgt;
    }

    public function getCreated()
    {
        return $this->created;
    }

    public function getUpdated()
    {
        return $this->updated;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function setLanguage($language)
    {
        $this->language = $language;
    }

    public function getVisible()
    {
        return $this->visible;
    }

    public function setVisible($visible)
    {
        $this->visible = $visible;
    }

    public function getDefault()
    {
        return $this->default;
    }

    public function setDefault($default)
    {
        $this->default = $default;
    }

    public function getModule()
    {
        return $this->module;
    }

    public function setModule($module)
    {
        $this->module = $module;
    }

    public function getPresenter()
    {
        return $this->presenter;
    }

    public function setPresenter($presenter)
    {
        $this->presenter = $presenter;
    }

    public function getModuleName()
    {
        return $this->moduleName;
    }

    public function setModuleName($moduleName)
    {
        $this->moduleName = $moduleName;
    }

    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function setClass($class)
    {
        $this->class = $class;
    }

    public function __toString()
    {
        return $this->getTitle();
    }

    public function getBoxes()
    {
        return $this->boxes;
    }

    /**
     * @param Box[] $boxes
     */
    public function setBoxes($boxes)
    {
        $this->boxes = $boxes;
    }

    public function getBox($name)
    {
        foreach ($this->boxes as $box) {
            if ($box->getBox() === $name) {
                return $box;
            }
        }

        return NULL;
    }

    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    public function getRedirect()
    {
        return $this->redirect;
    }

    /**
     * @param boolean $redirect
     */
    public function setRedirect($redirect)
    {
        $this->redirect = $redirect;
    }

}
