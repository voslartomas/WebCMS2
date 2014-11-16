<?php

class ThumbnailTest extends \WebCMS\Tests\EntityTestCase
{
    protected $thumbnail;

    public function testCreateThumbnail()
    {
        $this->initThumbnail();

        $this->em->persist($this->thumbnail);
        $this->em->flush();

        $thumbnails = $this->em->getRepository('WebCMS\Entity\Thumbnail')->findAll();
        $this->assertEquals(true, $thumbnails[0]->getCrop());
        $this->assertEquals('key', $thumbnails[0]->getKey());
        $this->assertEquals(2, $thumbnails[0]->getResize());
        $this->assertEquals(true, $thumbnails[0]->getSystem());
        $this->assertEquals(true, $thumbnails[0]->getWatermark());
        $this->assertEquals(400, $thumbnails[0]->getX());
        $this->assertEquals(200, $thumbnails[0]->getY());
    }

    private function initThumbnail()
    {
        $this->thumbnail = new \WebCMS\Entity\Thumbnail();
        $this->thumbnail->setCrop(true);
        $this->thumbnail->setKey('key');
        $this->thumbnail->setResize(2);
        $this->thumbnail->setSystem(true);
        $this->thumbnail->setWatermark(true);
        $this->thumbnail->setX(400);
        $this->thumbnail->setY(200);
    }
}
