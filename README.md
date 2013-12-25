WebCMS2
=======

[![Total Downloads](https://poser.pugx.org/webcms2/webcms2/downloads.png)](https://packagist.org/packages/webcms2/webcms2)
[![Latest Stable Version](https://poser.pugx.org/webcms2/webcms2/v/stable.png)](https://github.com/ufik/WebCMS2/releases)

Content management system based on Nette Framework with Doctrine2 ORM library.

Contains libraries
------------------

Look into composer.json for all used libraries.

INSTALLATION
------------

Download [https://github.com/nette/sandbox](Nette sandbox), default vendor directory is 'libs'.

Add this line into your composer.json file.

```
"webcms2\webcms2" : "@dev"
```

This command will download all required packages, create DB schema, make all necessary directories and change mode for required files.

```
composer install
```

BOOTSTRAP example
--

```
<?php

/**
 * My Application bootstrap file.
 */
use Nette\Application\Routers\Route;


// Load Nette Framework
require LIBS_DIR . '/autoload.php';

// Configure application
$configurator = new Nette\Config\Configurator;

// detects environment via virtual server name
if(PHP_SAPI === 'cli'){
	if(php_uname('n') === 'YOUR PRODUCTION SERVER NAME'){
		$environment = 'production';
	}else{
		$environment = 'development';
	}
}else{
	$environment = NULL;
}

// Enable Nette Debugger for error visualisation & logging
$configurator->enableDebugger(__DIR__ . '/../log');

// Enable RobotLoader - this will load all classes automatically
$configurator->setTempDirectory(__DIR__ . '/../temp');
$configurator->createRobotLoader()
	->addDirectory(APP_DIR)
	->register();

// Create Dependency Injection container from config.neon file
if(!$environment){
	$configurator->addConfig(__DIR__ . '/config/config.neon');
	$configurator->addConfig(LIBS_DIR . '/webcms2/webcms2/config.neon');
}
else{
	$configurator->addConfig(__DIR__ . '/config/config.neon', $environment);
	$configurator->addConfig(LIBS_DIR . '/webcms2/webcms2/config.neon', $environment);
}

\Nella\Console\Config\Extension::register($configurator);
\Nella\Doctrine\Config\Extension::register($configurator);
\Nella\Doctrine\Config\MigrationsExtension::register($configurator);
\Nella\Doctrine\Config\GedmoExtension::register($configurator);

$container = $configurator->createContainer();

// Setup router
$container->router[] =  new Route('', array(
	'module' => 'Frontend',
	'presenter' => 'Homepage',
	'action' => 'default'
));

$container->router[] =  new Route('login', array(
	'module' => 'Admin',
	'presenter' => 'Login',
	'action' => 'default'
));

$container->router[] =  new Route('admin/<presenter>/<action>[/<id>]', array(
	'module' => 'Admin',
	'presenter' => 'Homepage',
	'action' => 'default'
));

$entityManager = $container->getService('doctrine.entityManager');
$container->router[] = new WebCMS\SystemRouter($entityManager);

// Configure and run the application!
$container->application->run();
```

CONFIG.neon example
--

There is `boxes` section, here defined boxes are shown in page settings.

Another important setting is `cacheNamespace`, which defines project specific namespace. If you use memcached cache storage, there can be a problem if two projects have same namespace.

``
common:
	parameters:
		database:
			driver: pdo_mysql
			host: localhost
			dbname: nameOfDatabase
			user: root
			password:
			charset: utf8
			collation: utf8_czech_ci
		boxes:
			box1: true
			box2: true
			box3: true

		cacheNamespace: 'projectNamespace'

production < common:
	parameters:
		database:
			password: 

development < common:
``


