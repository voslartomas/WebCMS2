<?php

class BreadcrumbsItemTest extends \WebCMS\Tests\EntityTestCase
{
    public function testBox()
    {
	    $breadcrumbsItem = new \WebCMS\Entity\BreadcrumbsItem(1, 'Module', 'Presenter', 'Title', 'path');

	    $this->assertEquals(1, $breadcrumbsItem->getId());
	    $this->assertEquals('Module', $breadcrumbsItem->getModuleName());
	    $this->assertEquals('Presenter', $breadcrumbsItem->getPresenter());
	    $this->assertEquals('Title', $breadcrumbsItem->getTitle());
	    $this->assertEquals('path', $breadcrumbsItem->getPath());

	    $breadcrumbsItem->setId(2);
	    $breadcrumbsItem->setModuleName('Module1');
	    $breadcrumbsItem->setPresenter('Presenter1');
	    $breadcrumbsItem->setTitle('Title1');
	    $breadcrumbsItem->setPath('path1');

	    $this->assertEquals(2, $breadcrumbsItem->getId());
	    $this->assertEquals('Module1', $breadcrumbsItem->getModuleName());
	    $this->assertEquals('Presenter1', $breadcrumbsItem->getPresenter());
	    $this->assertEquals('Title1', $breadcrumbsItem->getTitle());
	    $this->assertEquals('path1', $breadcrumbsItem->getPath());
    }
}
