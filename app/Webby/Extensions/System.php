<?php

namespace Webby\Extensions;


use Nette\Application\IRouter;
use Nette\DI\CompilerExtension;
use Nette\DI\Statement;
use Nette\Http\Request;
use Nette\Http\UrlScript;
use Webby\System\Particles;
use Webby\System\Assets;
use Webby\System\Menus;
use Webby\System\Pages;
use Webby\System\Robots;
use Webby\System\LinkGenerator;
use Webby\System\Sitemap;
use Webby\System\Theme;

class System extends CompilerExtension
{

    private $defaults = [
        "url" => null,
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
            "description" => null,
            "head" => [],
            "body" => []
        ],
        "menus" => [],
        "particles" => [],
        "sitemap" => [
            "enabled" => true
        ],
        "robots" => [
            "enabled" => true,
            "sitemap" => true,
            "disallow" => [],
            "allow" => []
        ]
    ];

    public function loadConfiguration()
    {
        $config = $this->getConfig($this->defaults);
        $builder = $this->getContainerBuilder();

        // Pages
        $builder->addDefinition($this->prefix('pages'))
            ->setClass(
                Pages::class,
                [
                    $config["pages"]
                ]
            );

        // LinkGenerator
        $builder->addDefinition($this->prefix('router'))
            ->setClass(LinkGenerator::class);

        // Register pages route
        $builder->getDefinitionByType(IRouter::class)
            ->addSetup(
                '$service[] = new ' . Pages\Route::class . '(?)',
                [
                    $builder->getDefinitionByType(Pages::class)
                ]
            );

        // Particles
        $builder->addDefinition($this->prefix('particles'))
            ->setClass(Particles::class, [$config["particles"]]);

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
            )
            ->addSetup(
                '$service->registerCallback(' . Pages::class . '::createSitemapCb(?, ?, ?))',
                [
                    $builder->getDefinitionByType(Pages::class),
                    $builder->getDefinitionByType(LinkGenerator::class),
                    $builder->getDefinitionByType(IRouter::class)
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

        // CLI setup
        if (PHP_SAPI === 'cli') {
            $builder->getDefinition('http.request')
                ->setClass(Request::class, [new Statement(UrlScript::class, [$config['url']])]);
        }
    }

}