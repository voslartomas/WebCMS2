<?php

namespace WebCMS\Helpers;

/**
 * Primitive object class for RSS item.
 * @author Tomas Voslar <tomas.voslar at webcook.cz>
 */
class RssItem
{
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
	 * [$publishDate description]
	 * @var [type]
	 */
	private $publishDate;

    /**
     * Gets the [$title description].
     *
     * @return [type]
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the [$title description].
     *
     * @param [type] $title the title
     *
     * @return self
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Gets the [$link description].
     *
     * @return [type]
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Sets the [$link description].
     *
     * @param [type] $link the link
     *
     * @return self
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Gets the [$description description].
     *
     * @return [type]
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets the [$description description].
     *
     * @param [type] $description the description
     *
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Gets the [$publishDate description].
     *
     * @return [type]
     */
    public function getPublishDate()
    {
        return $this->publishDate;
    }

    /**
     * Sets the [$publishDate description].
     *
     * @param [type] $publishDate the publish date
     *
     * @return self
     */
    public function setPublishDate($publishDate)
    {
        $this->publishDate = $publishDate->format('M d Y H:i:s');

        return $this;
    }
}