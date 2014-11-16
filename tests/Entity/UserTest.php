<?php

class UserTest extends \WebCMS\Tests\EntityTestCase
{
    protected $user;

    public function testUser()
    {
        $this->initUser();

        $this->em->persist($this->user);
        $this->em->flush();

        $users = $this->em->getRepository('WebCMS\Entity\User')->findAll();

        $this->assertEquals('email@domain.at', $users[0]->getEmail());
        $this->assertEquals('Name', $users[0]->getName());
        $this->assertEquals('password', $users[0]->getPassword());
        $this->assertEquals('username', $users[0]->getUsername());
        $this->assertInstanceOf('WebCMS\Entity\Role', $users[0]->getRole());

        $this->em->remove($users[0]->getRole());
        $this->em->remove($users[0]);

        $this->em->flush();

        $users = $this->em->getRepository('WebCMS\Entity\User')->findAll();

        $this->assertEquals(0, count($users));
    }

    private function initUser()
    {
        $role = new \WebCMS\Entity\Role();
        $role->setAutomaticEnable(true);
        $role->setName('Role');

        $this->em->persist($role);

        $this->user = new WebCMS\Entity\User();
        $this->user->setEmail('email@domain.at');
        $this->user->setName('Name');
        $this->user->setPassword('password');
        $this->user->setUsername('username');
        $this->user->setRole($role);
    }
}
