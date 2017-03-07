<?php

// Load DI container
$container = require __DIR__ . '/../app/bootstrap.php';

// Run the application!
$container->getByType(Nette\Application\Application::class)->run();
