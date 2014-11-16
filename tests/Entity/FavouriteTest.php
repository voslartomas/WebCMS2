<?php

class FavouriteTest extends \WebCMS\Tests\EntityTestCase
{
    protected $favourite;

    public function testCreateBox()
    {
        $this->initFavourite();

        $this->em->persist($this->favourite);
        $this->em->flush();

        $favourites = $this->em->getRepository('WebCMS\Entity\Favourites')->findAll();

        $this->assertEquals(1, count($favourites));
        $this->assertEquals('favourite', $favourites[0]->getTitle());
        $this->assertEquals('http://link.com', $favourites[0]->getLink());
        $this->assertInstanceOf('WebCMS\Entity\User', $favourites[0]->getUser());

        $this->em->remove($favourites[0]->getUser());
        $this->em->remove($favourites[0]);

        $this->em->flush();

        $favourites = $this->em->getRepository('WebCMS\Entity\Favourites')->findAll();

        $this->assertCount(0, $favourites);
    }

    private function initFavourite()
    {
        $user = new \WebCMS\Entity\User();
        $user->setEmail('email');
        $user->setName('name');
        $user->setUsername('username');
        $user->setPassword('password');

        $this->em->persist($user);

        $this->favourite = new WebCMS\Entity\Favourites();
        $this->favourite->setLink('http://link.com');
        $this->favourite->setTitle('favourite');
        $this->favourite->setUser($user);
    }
}
