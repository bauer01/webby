<?php

namespace Webby\DI;


use Nette\DI\CompilerExtension;
use Nette\Utils\Finder;

class PluginExtension extends CompilerExtension
{
    public $defaults = [
        "pluginDir" => null
    ];

    public function loadConfiguration()
    {
        $config = $this->getConfig($this->defaults);
        $builder = $this->getContainerBuilder();

    }

}