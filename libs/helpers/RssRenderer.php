<?php

namespace WebCMS\Helpers;

class RssRenderer
{
	/**
	 * [$items description]
	 * @var array<RssItem>
	 */
	private $items;

	/**
	 * [$title description]
	 * @var [type]
	 */
	private $title;

	/**
	 * [$link description]
	 * @var [type]
	 */
	private $link;

	/**
	 * [$description description]
	 * @var [type]
	 */
	private $description;

	/**
	 * Date of last publish of rss.
	 * 
	 * @var mixed
	 */
	private $publishDate;

	/**
	 * 
	 * @param array<RssItem> $items
	 */
	public function __construct($items = array(), $publishDate = null)
	{
		$this->items = $items;
		$this->publishDate = $publishDate;
	}

	/**
	 * [render description]
	 * @return [type]
	 */
	public function render($render = true)
	{
		if ($render) {
			ob_clean();
			header('Content-Type: application/rss+xml; charset=utf-8');
			echo $this->createXmlDocument();
			flush();
			die();	
		}
		
		return $this->createXmlDocument();
	}

	/**
	 * [createXmlDocument description]
	 * @return [type]
	 */
	public function createXmlDocument()
	{
		$dom = new \DOMDocument('1.0', 'utf-8');
   		$dom->formatOutput = TRUE;
 		$rss = $dom->appendChild($dom->createElement('rss'));

  		$version = $dom->createAttribute('version');
  		$version->appendChild($dom->createTextNode('2.0'));
 		$rss->appendChild($version);
  
 		$datetime = $this->publishDate ? $this->publishDate : new \DateTime('now');
 		$datetime = $datetime->format('M d Y H:i:s');

 		$channel = $rss->appendChild($dom->createElement('channel'));
  		$channel->appendChild($dom->createElement('title', $this->title));
  		$channel->appendChild($dom->createElement('link', $this->link));
  		$channel->appendChild($dom->createElement('description', $this->description));
		$channel->appendChild($dom->createElement('lastBuildDate', $datetime));
		$channel->appendChild($dom->createElement('pubDate', $datetime));

		foreach ($this->items as $item) {
			$description = $item->getDescription();
			$description = str_replace(array("\r\n", "\r", "\n"), "<br /> ", $description);
			$description = html_entity_decode(strip_tags($description), ENT_QUOTES, 'utf-8');

			$node = $channel->appendChild($dom->createElement('item'));
			$node->appendChild($dom->createElement('title', $item->getTitle()));
			$node->appendChild($dom->createElement('link', $item->getLink()));
			$node->appendChild($dom->createElement('description', $description));
			$node->appendChild($dom->createElement('pubDate', $item->getPublishDate()));
		}

		return $dom->saveXML();
	}
}