<?php
    
// Load Nette Framework
if(file_exists(__DIR__ . '/../vendor/autoload.php')){
    require __DIR__ . '/../vendor/autoload.php';

    require_once __DIR__ . '/DummyUserStorage.php';
    
    \Nette\Diagnostics\Debugger::enable(false);
    \Nette\Diagnostics\Debugger::$strictMode = true;
    
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
    
    $container->removeService('nette.userStorage');
    $container->addService('nette.userStorage', new \WebCMS\Tests\DummyUserStorage());
    
    // Setup router
    $container->router[] =  new \Nette\Application\Routers\Route('', array(
            'module' => 'Frontend',
            'presenter' => 'Homepage',
            'action' => 'default'
    ));

    $container->router[] =  new \Nette\Application\Routers\Route('login', array(
            'module' => 'Admin',
            'presenter' => 'Login',
            'action' => 'default'
    ));
    
    $container->router[] =  new \Nette\Application\Routers\Route('admin/<presenter>/<action>[/<id>]', array(
            'module' => 'Admin',
            'presenter' => 'Homepage',
            'action' => 'default'
    ));
    
    Nette\Environment::getSession()->start();
}