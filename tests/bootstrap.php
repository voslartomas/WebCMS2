<?php
    
// Load Nette Framework
if(file_exists(__DIR__ . '/../vendor/autoload.php')){
    require __DIR__ . '/../vendor/autoload.php';

    // Configure application
    $configurator = new Nette\Config\Configurator;

    \Nella\Console\Config\Extension::register($configurator);
    \Nella\Doctrine\Config\Extension::register($configurator);
    \Nella\Doctrine\Config\MigrationsExtension::register($configurator);
    \Nella\Doctrine\Config\GedmoExtension::register($configurator);

    $configurator->addConfig(__DIR__ . '/config.neon');

    $configurator->enableDebugger(__DIR__ . '/temp/log');
    $configurator->setTempDirectory(__DIR__ . '/temp');

    $container->router[] =  new Nette\Application\Routers\Route('admin/<presenter>/<action>[/<id>]', array(
            'module' => 'Admin',
            'presenter' => 'Homepage',
            'action' => 'default'
    ));
    
    $container = $configurator->createContainer();
}