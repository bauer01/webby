<?php

namespace Webby\Extensions;


use Nette\DI\CompilerExtension;
use Webby\System\Particles;
use Webby\System\Assets;
use Webby\System\Menus;
use Webby\System\Pages;
use Webby\System\Robots;
use Webby\System\Sitemap;
use Webby\System\Theme;

class System extends CompilerExtension
{

    private $defaults = [
        "assets" => [
            "dir" => null,
            "mediaDir" => null
        ],
        "theme" => [
            "dir" => null,
            "current" => null
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
        "menus" => [],
        "particles" => null,
        "sitemap" => [
            "enabled" => true
        ],
        "robots" => [
            "enabled" => true,
            "disallow" => []
        ]
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

        // Particles
        $builder->addDefinition($this->prefix('particles'))
            ->setClass(Particles::class);

        // Assets
        $builder->addDefinition($this->prefix('assets'))
            ->setClass(Assets::class, [$config["assets"]]);

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

        // Sitemap
        $builder->addDefinition($this->prefix('sitemap'))
            ->setClass(
                Sitemap::class,
                [
                    $config["sitemap"]
                ]
            );

        // Robots
        $builder->addDefinition($this->prefix('robots'))
            ->setClass(
                Robots::class,
                [
                    $config["robots"]
                ]
            );
    }

}