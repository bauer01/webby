<?php

namespace Webby\Extensions;


use Assetic\Asset\AssetCollection;
use Nette\DI\CompilerExtension;
use Webby\System\Menus;
use Webby\System\Pages;
use Webby\System\Theme;

class System extends CompilerExtension
{

    private $defaults = [
        "theme" => [
            "dir" => null,
            "current" => null,
            "assetsDir" => null
        ],
        "pages" => [
            "dir" => null,
            "homepage" => null,
            "language" => "en",
            "charset" => "utf-8",
            "title" => null,
            "title_delimiter" => "-",
            "description" => null
        ],
        "menus" => []
    ];

    public function loadConfiguration()
    {
        $config = $this->getConfig($this->defaults);
        $builder = $this->getContainerBuilder();

        // Pages
        $builder->getDefinition("router")
            ->addSetup(
                '$service[] = ' . Pages::class . '::createRoute(?)',
                [
                    $config["pages"]
                ]
            );
        $builder->addDefinition($this->prefix('pages'))
            ->setClass(
                Pages::class,
                [
                    $config["pages"]
                ]
            );

        // JS
        $builder->addDefinition($this->prefix('js'))
            ->setClass(AssetCollection::class)
            ->addSetup("setTargetPath", ["scripts.js"]);

        // CSS
        $builder->addDefinition($this->prefix('css'))
            ->setClass(AssetCollection::class)
            ->addSetup("setTargetPath", ["styles.css"]);

        // Theme
        $builder->addDefinition($this->prefix('theme'))
            ->setClass(
                Theme::class,
                [
                    $config["theme"]
                ]
            );

        // Menus
        $builder->addDefinition($this->prefix('menus'))
            ->setClass(
                Menus::class,
                [
                    $config["menus"]
                ]
            );
    }

}