<?php

class AuthenticatorTest extends \WebCMS\Tests\PresenterTestCase
{
    public function testAuthentication()
    {
	    $authenticator = new \Authenticator($this->em->getRepository('\WebCMS\Entity\User'));

	    $identity = $authenticator->authenticate(array(
	        'test', 'test'
	    ));

	    $this->assertInstanceOf('\Nette\Security\IAuthenticator', $authenticator);
	    $this->assertInstanceOf('\Nette\Security\Identity', $identity);
    }
}
