<?php

namespace Webby\Extensions\Menus;

use Nette\DI\CompilerExtension;
use Webby\System\Menus;

class Extension extends CompilerExtension
{

    public function loadConfiguration()
    {
        $config = $this->getConfig($this->defaults);
        $builder = $this->getContainerBuilder();

        // Items service
        $builder->addDefinition($this->prefix('menus'))
            ->setClass(Menus::class, [$config["items"]]);
    }

}