<?php

// hack for https proxies
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
    if ($_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' && isset($_SERVER['SERVER_PORT']) && in_array($_SERVER['SERVER_PORT'], [80, 82])) { // https over proxy
        $_SERVER['HTTPS'] = 'On';
        $_SERVER['SERVER_PORT'] = 443;
    } elseif ($_SERVER['HTTP_X_FORWARDED_PROTO'] === 'http' && isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 80) { // http over proxy
        $_SERVER['HTTPS'] = 'Off';
        $_SERVER['SERVER_PORT'] = 80;
    }
}

define('WWW_DIR', __DIR__ . "/../html");

// Load dependencies
if (@!$composer = include __DIR__ . '/../vendor/autoload.php') {
    die('Install dependencies using `composer update`');
}

$appDir = __DIR__ . "/..";

// Configure application
$configurator = new Nette\Configurator;

// Enable Tracy for error visualisation & logging
$configurator->setDebugMode((bool) getenv("WEBBY_DEBUG"));
$configurator->enableTracy($appDir . '/log');

// Create Dependency Injection container
$configurator->setTempDirectory($appDir . '/temp');
$configurator->addConfig(__DIR__ . '/config.neon');

$settingsFile = __DIR__ . "/../content/settings.neon";
if (is_file($settingsFile)) {
    $configurator->addConfig($settingsFile);
}
return $configurator->createContainer();
