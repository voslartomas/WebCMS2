<?php
    
// Load Nette Framework
require __DIR__ . '/../../../autoload.php';

// Configure application
$configurator = new Nette\Config\Configurator;

\Nella\Console\Config\Extension::register($configurator);
\Nella\Doctrine\Config\Extension::register($configurator);
\Nella\Doctrine\Config\MigrationsExtension::register($configurator);
\Nella\Doctrine\Config\GedmoExtension::register($configurator);

$configurator->addConfig(__DIR__ . '/config.neon');

$configurator->enableDebugger(__DIR__ . '/temp/log');
$configurator->setTempDirectory(__DIR__ . '/temp');

$container = $configurator->createContainer();

// Configure and run the application!
//$container->application->run();