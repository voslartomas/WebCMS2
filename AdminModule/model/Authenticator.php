<?php

use Nette\Security as NS;

/**
 * Users authenticator.
 *
 * @author     Tomáš Voslař <tomas.voslar at webcook.cz>
 * @package    WebCMS2
 */
class Authenticator extends Nette\Object implements NS\IAuthenticator {

    /** @var \Doctrine\ORM\EntityRepository */
    private $users;

    public function __construct(\Doctrine\ORM\EntityRepository $users) {
	$this->users = $users;
    }

    /**
     * Performs an authentication
     * @param  array
     * @return Nette\Security\Identity
     * @throws Nette\Security\AuthenticationException
     */
    public function authenticate(array $credentials) {
	list($username, $password) = $credentials;
	$user = $this->users->findOneBy(array('username' => $username));

	if (!$user) {
	    throw new NS\AuthenticationException("User not found.", self::IDENTITY_NOT_FOUND);
	}

	if ($user->password !== $this->calculateHash($password)) {
	    throw new NS\AuthenticationException("Invalid password.", self::INVALID_CREDENTIAL);
	}

	$permissions = array();
	foreach ($user->getRole()->getPermissions() as $key => $per) {
	    $permissions[$per->getResource()] = $per->getRead();
	}

	return new NS\Identity($user->id, $user->getRole()->getName(), array(
	    'username' => $user->username,
	    'email' => $user->email,
	    'permissions' => $permissions
	));
    }

    /**
     * Computes salted password hash.
     * @param  string
     * @return string
     */
    public function calculateHash($password) {
	return md5($password . str_repeat('*feijfččí489*', 10));
    }

}
