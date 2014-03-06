<?php

namespace WebCMS\Tests;

class DummyUserStorage implements \Nette\Security\IUserStorage
{

    private $authenticated;

    private $identity;

    public function setAuthenticated($state)
    {
        $this->authenticated = $state;
    }

    public function isAuthenticated()
    {
        return $this->authenticated;
    }

    public function setIdentity(\Nette\Security\IIdentity $identity = NULL)
    {
        $this->identity = $identity;
    }

    public function getIdentity()
    {
        return $this->identity;
    }

    public function setExpiration($time, $flags = 0)
    {
    }

    public function getLogoutReason()
    {
        return NULL;
    }

}