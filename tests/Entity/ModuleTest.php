<?php

class ModuleTest extends \WebCMS\Tests\EntityTestCase
{
    protected $module;

    public function testCreateModule()
    {
        $this->initModule();

        $this->em->persist($this->module);
        $this->em->flush();

        $modules = $this->em->getRepository('WebCMS\Entity\Module')->findAll();

        $this->assertEquals(1, count($modules));
        $this->assertEquals(true, $modules[0]->getActive());
        $this->assertEquals('Test', $modules[0]->getName());
        $this->assertEquals(array(
            array('name' => 'PresenterTest'),
        ), $modules[0]->getPresenters());
    }

    private function initModule()
    {
        $this->module = new \WebCMS\Entity\Module();
        $this->module->setActive(true);
        $this->module->setName('Test');
        $this->module->setPresenters(array(
            array('name' => 'PresenterTest'),
        ));
    }
}
