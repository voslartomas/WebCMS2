<?php
    
class BoxTest extends \WebCMS\Tests\EntityTestCase {
    
    protected $box; 
    
    public function testCreateBox() {
        
	$this->initBox();
	
	$this->em->persist($this->box);
	$this->em->flush();
	
	$boxes = $this->em->getRepository('WebCMS\Entity\Box')->findAll();
	
	$this->assertEquals(1, count($boxes));
	$this->assertEquals('box1', $boxes[0]->getBox());
	$this->assertEquals('function', $boxes[0]->getFunction());
	$this->assertEquals('presenter', $boxes[0]->getPresenter());
	$this->assertEquals('Module', $boxes[0]->getModuleName());
	$this->assertInstanceOf('WebCMS\Entity\Page', $boxes[0]->getPageFrom());
	$this->assertInstanceOf('WebCMS\Entity\Page', $boxes[0]->getPageTo());
    }
    
    private function setPage($text = 'test'){
	$page = new \WebCMS\Entity\Page;
	$page->setTitle($text);
	$page->setPresenter($text);
	$page->setPath($text);
	$page->setVisible(true);
	$page->setDefault(true);
	$page->setClass('');
	
	return $page;
    }
    
    private function initBox(){
	$pageFrom = $this->setPage('Page from');
	$pageTo = $this->setPage('Page to');
	
	$this->em->persist($pageFrom);
	$this->em->persist($pageTo);
	
	$this->box = new WebCMS\Entity\Box;
	$this->box->setBox('box1');
	$this->box->setFunction('function');
	$this->box->setPresenter('presenter');
	$this->box->setModuleName('Module');
	$this->box->setPageFrom($pageFrom);
	$this->box->setPageTo($pageTo);
    }
}
