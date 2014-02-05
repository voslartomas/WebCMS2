<?php

class BoxTest extends PHPUnit_Framework_TestCase {
    
    protected $box;
    
    protected function setUp() {
        $pageFrom = new \AdminModule\Page;
        $pageFrom->setTitle('Test page from');
        
        $pageTo = new \AdminModule\Page;
        $pageTo->setTitle('Test page to');
        
        $this->box = new AdminModule\Box;
        $this->box->setBox('Box1');
        $this->box->setFunction('function');
        $this->box->setModuleName('Module');
        $this->box->setPresenter('presenter');
        $this->box->setPageFrom($pageFrom);
        $this->box->setPageTo($pageTo);
    }
    
    public function testProcess() {
        
        $this->assertEquals('Box1', $box->getBox());
        $this->assertEquals('function', $box->getBox());
        $this->assertEquals('Module', $box->getBox());
        $this->assertEquals('presenter', $box->getBox());
        $this->assertEquals('Test page from', $box->getPageFrom()->getTitle());
        $this->assertEquals('Test page to', $box->getPageTo()->getTitle());
    }
}
