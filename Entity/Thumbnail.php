<?php

namespace WebCMS\Entity;

use Doctrine\ORM\Mapping as orm;

/**
 * @orm\Entity
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class Thumbnail extends Entity
{
    /**
     * @ORM\Column(name="`key`")
     * @var String
     */
    private $key;

    /**
     * @ORM\Column(type="integer")
     * @var Int
     */
    private $x;

    /**
     * @ORM\Column(type="integer")
     * @var Int
     */
    private $y;

    /**
     * @ORM\Column(type="boolean")
     * @var Boolean
     */
    private $watermark;

    /**
     * @ORM\Column(type="boolean")
     * @var Boolean
     */
    private $system;

    /**
     * @ORM\Column(type="integer")
     * @var Int
     */
    private $resize;

    /**
     * @ORM\Column(type="boolean")
     * @var
     */
    private $crop;

    public function getCrop()
    {
        return $this->crop;
    }

    public function setCrop($crop)
    {
        $this->crop = $crop;
    }

    public function getResize()
    {
        return $this->resize;
    }

    public function setResize($resize)
    {
        $this->resize = $resize;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function setKey($key)
    {
        $this->key = $key;
    }

    public function getX()
    {
        return $this->x == 0 ? null : $this->x;
    }

    public function setX($x)
    {
        $this->x = $x;
    }

    public function getY()
    {
        return $this->y == 0 ? null : $this->y;
    }

    public function setY($y)
    {
        $this->y = $y;
    }

    public function getWatermark()
    {
        return $this->watermark;
    }

    public function setWatermark($watermark)
    {
        $this->watermark = $watermark;
    }

    public function getSystem()
    {
        return $this->system;
    }

    public function setSystem($system)
    {
        $this->system = $system;
    }
}
