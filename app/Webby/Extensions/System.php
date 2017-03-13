<?php

namespace Webby\Extensions;


use Assetic\Asset\AssetCollection;
use Nette\DI\CompilerExtension;
use Webby\Presenter\DefaultPresenter;
use Webby\Routing\Route;
use Webby\System\Theme;

class System extends CompilerExtension
{

    private $defaults = [
        "assetsDir" => null,
        "pagesDir" => null,
        "themeDir" => null,
        "homepage" => null,
        "language" => "en",
        "charset" => "utf-8",
        "title" => null,
        "title_delimiter" => "-",
        "description" => null,
        "email" => null,
        "theme" => null,
        "layout" => null
    ];

    public function loadConfiguration()
    {
        $config = $this->getConfig($this->defaults);
        $builder = $this->getContainerBuilder();

        // Pages
        $builder->getDefinition("router")
            ->addSetup(
                '$service[] = ?',
                new Route(function($link) use ($config) {

                    if (!$link) {
                        $link = $config["homepage"];
                    }

                    if (is_file($this->dir . "/" . Route::linkToPath($link) . ".yml")) {
                        $pageConfig = self::loadPageConfig($config["pagesDir"], $link);
                        return [
                            "callback" => function (DefaultPresenter $presenter) use ($link, $pageConfig) {
                                return $presenter->createPageResponse($link, $pageConfig);
                            },
                            "pageConfig" => $pageConfig
                        ];
                    }
                    return false;
                })
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
                    $config["layout"],
                    $config["themeDir"],
                    $config["theme"],
                    $config["assetsDir"]
                ]
            );
    }

    public static function loadPageConfig($dir, $link)
    {
        // @todo caching
        $config = yaml_parse_file($dir . "/" . Route::linkToPath($link) . ".yml");
        if (!empty($config["extends"])) {
            // @todo intelligent merge
            $config += self::loadPageConfig($dir, $config["extends"]);
        }
        return $config;
    }

}