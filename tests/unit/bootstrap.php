<?php

$loader = @require __DIR__ . '/../../vendor/autoload.php';
if (!$loader) {
    echo 'Install Nette dependencies using `composer update --dev`';
    exit(1);
}
Tester\Environment::setup();
date_default_timezone_set("Europe/Prague");