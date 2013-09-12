<?php

namespace AdminModule;

use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as gedmo;
use Doctrine\orm\Mapping as orm;

/**
 * Description of Page
 * @gedmo\Tree(type="nested")
 * @orm\Entity(repositoryClass="PageRepository")
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class Page extends Seo{

    /**
     * @orm\Column(length=64)
     */
    private $title;
	
    /**
     * @orm\Column(type="text", nullable=true)
     */
    private $description;

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
	 * @orm\Column(type="boolean")
	 */
	private $visible;
	
	/**
	 * @orm\Column(type="boolean", name="`default`")
	 */
	private $default;
	
    public function __construct()    {
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

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getDescription()
    {
        return $this->description;
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
	
	public function getLanguage() {
		return $this->language;
	}

	public function setLanguage($language) {
		$this->language = $language;
	}
	
	public function getVisible() {
		return $this->visible;
	}

	public function setVisible($visible) {
		$this->visible = $visible;
	}

	public function getDefault() {
		return $this->default;
	}

	public function setDefault($default) {
		$this->default = $default;
	}
	
    public function __toString(){
        return $this->getTitle();
    }
	
}
