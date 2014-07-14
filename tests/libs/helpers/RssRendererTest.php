<?php

class RssRendererTest extends \WebCMS\Tests\EntityTestCase
{
    public function testRender()
    {
    	$datetime = new DateTime('now');

        $rssItem = new \WebCMS\Helpers\RssItem;
        $rssItem->setTitle('Article');
        $rssItem->setLink('http://www.article.com');
        $rssItem->setDescription('Article description');
        $rssItem->setPublishDate($datetime);

        $rssItem2 = new \WebCMS\Helpers\RssItem;
        $rssItem2->setTitle('Article');
        $rssItem2->setLink('http://www.article.com');
        $rssItem2->setDescription('Article description');
        $rssItem2->setPublishDate($datetime);
        
        $items = array($rssItem, $rssItem2);

        $rssRenderer = new \WebCMS\Helpers\RssRenderer($items, $datetime);
        $rssRenderer->setTitle('Articles');
        $rssRenderer->setLink('http://www.domain.tld');
        $rssRenderer->setDescription('Articles description');

        $xml = $rssRenderer->render(false);

        $this->assertEquals('<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0">
  <channel>
    <title>Articles</title>
    <link>http://www.domain.tld</link>
    <description>Articles description</description>
    <lastBuildDate>' . $datetime->format('M d Y H:i:s') . '</lastBuildDate>
    <pubDate>' . $datetime->format('M d Y H:i:s') . '</pubDate>
    <item>
      <title>Article</title>
      <link>http://www.article.com</link>
      <description>Article description</description>
      <pubDate>' . $datetime->format('M d Y H:i:s') . '</pubDate>
    </item>
    <item>
      <title>Article</title>
      <link>http://www.article.com</link>
      <description>Article description</description>
      <pubDate>' . $datetime->format('M d Y H:i:s') . '</pubDate>
    </item>
  </channel>
</rss>
', $xml);

    }       
}
