<?php

namespace WebCMS\Entity;

use Doctrine\ORM\Mapping as orm;

/**
 * @orm\Entity
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class Language extends Entity
{
    /**
     * @orm\Column
     * @var String
     */
    private $name;

    /**
     * @orm\Column
     * @var String
     */
    private $abbr;

    /**
     * @orm\Column(type="boolean")
     * @var Boolean
     */
    private $defaultFrontend;

    /**
     * @orm\Column(type="boolean")
     * @var Boolean
     */
    private $defaultBackend;

    /**
     * @orm\OneToMany(targetEntity="Translation", mappedBy="language")
     * @var Array
     */
    private $translations;

    /**
     * @orm\Column
     */
    private $locale;

    public function getTranslations()
    {
        return $this->translations;
    }

    public function setTranslations(Array $translations)
    {
        $this->translations = $translations;
    }

    public function getDefaultBackend()
    {
        return $this->defaultBackend;
    }

    public function setDefaultBackend($defaultBackend)
    {
        $this->defaultBackend = $defaultBackend;
    }

    public function getDefaultFrontend()
    {
        return $this->defaultFrontend;
    }

    public function setDefaultFrontend($defaultFrontend)
    {
        $this->defaultFrontend = $defaultFrontend;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getAbbr()
    {
        return $this->abbr;
    }

    public function setAbbr($abbr)
    {
        $this->abbr = $abbr;

        return $this;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

}
