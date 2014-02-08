<?php
    
class BreadcrumbsItemTest extends \WebCMS\Tests\EntityTestCase {
    
    public function testBox() {
        
	$breadcrumbsItem = new \WebCMS\Entity\BreadcrumbsItem;
	$breadcrumbsItem->setId(1);
	$breadcrumbsItem->setModuleName('Module');
	$breadcrumbsItem->setPath('Path');
	$breadcrumbsItem->setPresenter('Presenter');
	$breadcrumbsItem->setTitle('Title');
    }
}