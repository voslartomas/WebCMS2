<?php

class RoleTest extends \WebCMS\Tests\EntityTestCase
{
    protected $role;

    public function testRole()
    {
        $this->initRole();

        $this->em->persist($this->role);
        $this->em->flush();

        $roles = $this->em->getRepository('WebCMS\Entity\Role')->findAll();

        $this->assertCount(1, $roles);
        $this->assertEquals('Role', $roles[0]->getName());
        $this->assertTrue($roles[0]->getAutomaticEnable());
        $this->assertInstanceOf('WebCMS\Entity\Permission', $roles[0]->getPermissions()[0]);

        $this->em->remove($roles[0]->getPermissions()[0]);
        $this->em->remove($roles[0]);

        $this->em->flush();

        $roles = $this->em->getRepository('WebCMS\Entity\Role')->findAll();

        $this->assertCount(0, $roles);
    }

    private function initRole()
    {
        $permission = new WebCMS\Entity\Permission;
        $permission->setRead(true);
        $permission->setRemove(true);
        $permission->setWrite(true);
        $permission->setResource('Resource');

        $this->em->persist($permission);

        $this->role = new WebCMS\Entity\Role;
        $this->role->setAutomaticEnable(true);
        $this->role->setName('Role');
        $this->role->addPermission($permission);
    }
}
