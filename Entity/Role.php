<?php

namespace WebCMS\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Role
 * @ORM\Entity
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class Role extends Entity
{
    /**
     * @ORM\Column(unique=true)
     * @var string
     */
    private $name;

    /**
     * @orm\ManyToMany(targetEntity="Permission", cascade={"persist"})
     * @orm\JoinColumn(name="permission_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $permissions;

    /**
     * @orm\Column(type="boolean")
     */
    private $automaticEnable;

    public function __construct()
    {
        $this->permissions = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function addPermission(Permission $permission)
    {
        if (!$this->getPermissions()->contains($permission)) {
            $this->permissions->add($permission);
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getPermissions()
    {
        return $this->permissions;
    }

    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;
    }

    public function getAutomaticEnable()
    {
        return $this->automaticEnable;
    }

    public function setAutomaticEnable($automaticEnable)
    {
        $this->automaticEnable = $automaticEnable;
    }

}
