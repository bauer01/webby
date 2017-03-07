<?php

define('WWW_DIR', __DIR__ . "/../html");

// Load dependencies
if (@!include __DIR__ . '/../vendor/autoload.php') {
    die('Install dependencies using `composer update`');
}

$appDir = __DIR__ . "/..";

// Configure application
$configurator = new Nette\Configurator;

// Enable Tracy for error visualisation & logging
$configurator->setDebugMode(true);
$configurator->enableTracy($appDir . '/log');

// Create Dependency Injection container
$configurator->setTempDirectory($appDir . '/temp');
$configurator->addConfig(__DIR__ . '/config.neon');
return $configurator->createContainer();
