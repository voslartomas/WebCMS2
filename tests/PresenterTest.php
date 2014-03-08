<?php

namespace WebCMS\Tests;

use \Mockery as m;
        
abstract class PresenterTestCase extends EntityTestCase{
    
    protected $container;
    
    protected $language;
    
    protected $role;
    
    protected $user;
    
    public function setUp() {
	parent::setUp();
	
	global $container;
        
        $this->container = $container;
	
	$this->language = new \WebCMS\Entity\Language;
	$this->language->setName('English');
	$this->language->setAbbr('en');
	$this->language->setDefaultBackend(true);
	$this->language->setDefaultFrontend(true);
	$this->language->setLocale('en_EN.UTF-8');
	
	$this->role = new \WebCMS\Entity\Role;
	$this->role->setName('superadmin');
	$this->role->setAutomaticEnable(FALSE);
	
	$this->em->persist($this->role);
	$this->em->flush();
	
	$this->user = new \WebCMS\Entity\User;
	$this->user->setUsername('test');
	$this->user->setPassword($this->container->authenticator->calculateHash('test'));
	$this->user->setEmail('test@test.com');
	$this->user->setName('test');
	$this->user->setRole($this->role);
	
	$this->em->persist($this->language);
	$this->em->persist($this->user);
	$this->em->flush();
	
	$user = new \Nette\Security\User($container->getService('nette.userStorage'), $this->container);
	$user->login('test', 'test');
    }
    
    public function tearDown() {
	parent::tearDown();
    }
}
    


