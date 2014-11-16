<?php

class PermissionTest extends \WebCMS\Tests\EntityTestCase
{
    protected $permission;

    public function testPermission()
    {
        $this->initPermission();

        $this->em->persist($this->permission);
        $this->em->flush();

        $permissions = $this->em->getRepository('WebCMS\Entity\Permission')->findAll();

        $this->assertCount(1, $permissions);
        $this->assertInstanceOf('WebCMS\Entity\Page', $permissions[0]->getPage());
        $this->assertTrue($permissions[0]->getRead());
        $this->assertTrue($permissions[0]->getRemove());
        $this->assertTrue($permissions[0]->getWrite());
        $this->assertEquals('Resource', $permissions[0]->getResource());

        $this->em->remove($permissions[0]->getPage());
        $this->em->remove($permissions[0]);

        $this->em->flush();

        $permissions = $this->em->getRepository('WebCMS\Entity\Permission')->findAll();

        $this->assertCount(0, $permissions);
    }

    private function initPermission()
    {
        $page = $this->setPage('Page');

        $this->em->persist($page);

        $this->permission = new WebCMS\Entity\Permission();
        $this->permission->setPage($page);
        $this->permission->setRead(true);
        $this->permission->setRemove(true);
        $this->permission->setWrite(true);
        $this->permission->setResource('Resource');
    }

    private function setPage($text = 'test')
    {
        $page = new \WebCMS\Entity\Page();
        $page->setTitle($text);
        $page->setPresenter($text);
        $page->setPath($text);
        $page->setVisible(true);
        $page->setDefault(true);
        $page->setClass('');

        return $page;
    }
}
