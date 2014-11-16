<?php

namespace WebCMS\Entity;

use Doctrine\ORM\Mapping as orm;

/**
 * Description of Settings
 * @ORM\Entity
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class Setting extends Entity
{
    /**
     * @ORM\Column(name="`key`")
     * @var String
     */
    private $key;

    /**
     * @ORM\Column(type="text")
     * @var String
     */
    private $value;

    /**
     * @ORM\Column
     * @var String
     */
    private $section;

    /**
     * @ORM\Column(nullable=true)
     * @var String
     */
    private $type;

    /**
     * @ORM\Column(nullable=true)
     * @var String
     */
    private $options;

    /**
     * @orm\ManyToOne(targetEntity="Language")
     * @orm\JoinColumn(name="language_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    private $language;

    public function getValue($raw = true, $fromPush = array(), $toPush = array())
    {
        if ($raw) {
            return $this->value;
        } else {
            return \WebCMS\Helpers\SystemHelper::replaceStatic($this->value, $fromPush, $toPush);
        }
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param Language $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    public function getSection()
    {
        return $this->section;
    }

    /**
     * @param string $section
     */
    public function setSection($section)
    {
        $this->section = $section;
    }

    public function getOptions()
    {
        return unserialize($this->options);
    }

    public function setOptions($options)
    {
        $this->options = serialize($options);
    }
}
