<?php

namespace WebCMS\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @property-read int $id
 * @property-read string $username
 * @property string $email
 * @property string $password
 * @property string $role
 */
class User extends Entity
{
    /**
     * @ORM\Column(unique=true)
     * @var string
     */
    private $username;

    /**
     * @ORM\Column
     * @var string
     */
    private $name;

    /**
     * @ORM\Column
     * @var string
     */
    private $email;

    /**
     * @ORM\Column
     * @var string
     */
    private $password;

    /**
     * @orm\ManyToOne(targetEntity="Role", fetch="EAGER")
     *
     * @orm\JoinColumn(name="role_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $role;

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string
     * @param  string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return Role
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param string
     * @param  Role $role
     * @return User
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }
}
