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
	if(php_uname('n') === 'vm7050'){
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
