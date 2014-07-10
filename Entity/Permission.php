<?php

namespace WebCMS\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class Permission extends Entity
{
    /**
     * @ORM\Column
     * @var string
     */
    private $resource;

    /**
     * @ORM\Column(type="boolean", name="`read`")
     * @var type
     */
    private $read;

    /**
     * @ORM\Column(type="boolean", name="`write`", nullable=true)
     * @var type
     */
    private $write;

    /**
     * @ORM\Column(type="boolean", name="`remove`", nullable=true)
     * @var type
     */
    private $remove;

    /**
     * @orm\ManyToOne(targetEntity="Page")
     * @orm\JoinColumn(onDelete="CASCADE")
     */
    private $page;

    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param string $resource
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
    }

    public function getRead()
    {
        return $this->read;
    }

    public function setRead($read)
    {
        $this->read = $read;
    }

    public function getWrite()
    {
        return $this->write;
    }

    public function setWrite($write)
    {
        $this->write = $write;
    }

    public function getRemove()
    {
        return $this->remove;
    }

    public function setRemove($remove)
    {
        $this->remove = $remove;
    }

    public function getPage()
    {
        return $this->page;
    }

    public function setPage($page)
    {
        $this->page = $page;
    }

}
