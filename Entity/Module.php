<?php

namespace WebCMS\Entity;

use Doctrine\ORM\Mapping as orm;

/**
 * @orm\Entity
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class Module extends Entity
{
    /**
     * @orm\Column
     */
    private $name;

    /**
     * @orm\Column(type="text")
     */
    private $presenters;

    /**
     * @orm\Column(type="boolean")
     */
    private $active;

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param boolean $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    public function getPresenters()
    {
        return unserialize($this->presenters);
    }

    public function setPresenters($presenters)
    {
        $this->presenters = serialize($presenters);
    }

}
