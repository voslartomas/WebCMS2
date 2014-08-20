<?php

class RssItemTest extends \WebCMS\Tests\EntityTestCase
{
    public function testEntity()
    {
    	$datetime = new DateTime('now');

        $rssItem = new \WebCMS\Helpers\RssItem;
        $rssItem->setTitle('Article');
        $rssItem->setLink('http://www.article.com');
        $rssItem->setDescription('Article description');
        $rssItem->setPublishDate($datetime);

        $this->assertEquals('Article', $rssItem->getTitle());
        $this->assertEquals('http://www.article.com', $rssItem->getLink());
        $this->assertEquals('Article description', $rssItem->getDescription());
        $this->assertEquals($datetime->format('M d Y H:i:s'), $rssItem->getPublishDate());
    }       
}
