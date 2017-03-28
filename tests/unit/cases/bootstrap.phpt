<?php

use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';

/**
 * @testCase
 */
class Bootstrap extends TestCase
{

    public function testContainer()
    {
        Assert::type("Nette\\DI\\Container", require __DIR__ . '/../../../app/bootstrap.php');
    }

}

$testCase = new Bootstrap();
$testCase->run();