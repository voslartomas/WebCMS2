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
    }
}
