<?php

namespace Webby\Extensions;


use Nette\DI\CompilerExtension;

class Menus extends CompilerExtension
{

    private $defaults = [
        "items" => []
    ];

    public function loadConfiguration()
    {
        $config = $this->getConfig($this->defaults);
        $builder = $this->getContainerBuilder();

        // Items
        $builder->addDefinition($this->prefix('items'))
            ->setClass(
                \Webby\System\Menus::class,
                [
                    $config["items"]
                ]
            );
    }

}