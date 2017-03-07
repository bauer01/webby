<?php

namespace Webby;

use Assetic\Asset\AssetCollection;
use Nette\Application\IRouter;
use Nette\Application\Routers\RouteList;
use Nette\DI\Container;
use Nette\Utils\Finder;
use Tracy\Debugger;
use Webby\Presenter\DefaultPresenter;
use Webby\Routing\Route;

class System
{

    const ASSETS_SCRIPTS = "scripts",
        ASSETS_STYLES = "styles";

    private $contentDir;
    private $themeDir;
    private $config;
    private $plugins = [];
    private $theme;
    private $wwwDir;
    private $container;
    private $assets = [];

    public function __construct($contentDir, $wwwDir, Container $container)
    {
        $this->config = yaml_parse_file($contentDir . "/system.yml");
        $this->contentDir = $contentDir;
        $this->pagesDir = $this->contentDir . "/pages";
        $this->themeDir = $this->contentDir . "/themes/" . $this->config["theme"];
        $this->wwwDir = $wwwDir;
        $this->container = $container;

        $this->router = new RouteList();
        $this->router[] = new Route(function($link) {

            if (!$link) {
                $link = $this->getConfig("homepage");
            }

            if (is_file($this->pagesDir . "/" . Route::linkToPath($link) . ".yml")) {
                $pageConfig = $this->loadPageConfig($link);
                return [
                    "callback" => function (DefaultPresenter $presenter) use ($link, $pageConfig) {
                        return $presenter->createPageResponse($link, $pageConfig);
                    },
                    "pageConfig" => $pageConfig
                ];
            }
            return false;
        });

        $this->assets[self::ASSETS_SCRIPTS] = $scripts = new AssetCollection();
        $scripts->setTargetPath(self::ASSETS_SCRIPTS . ".js");

        $this->assets[self::ASSETS_STYLES] = $styles = new AssetCollection();
        $styles->setTargetPath(self::ASSETS_STYLES . ".css");

        $this->theme = yaml_parse_file($this->themeDir . "/theme.yml")["config"] + yaml_parse_file($this->themeDir . ".yml");
        $this->loadPlugins();
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    public function addRoute(IRouter $route)
    {
        $this->router->prepend($route);
    }

    /**
     * @return mixed
     */
    public function getWwwDir()
    {
        return $this->wwwDir;
    }

    public function getAsset($name)
    {
        return $this->assets[$name];
    }

    /**
     * @return IRouter
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @return string
     */
    public function getPagesDir()
    {
        return $this->pagesDir;
    }

    public function getPlugin($name)
    {
        return $this->plugins[$name];
    }

    public function getThemeDir()
    {
        return $this->themeDir;
    }

    public function getTheme()
    {
        return $this->theme;
    }

    public function getLayout()
    {
        return $this->theme["layouts"][$this->theme["layout"]];
    }

    public function getConfig($key)
    {
        return $this->config[$key];
    }

    private function loadPlugins()
    {
        $pluginDir = $this->contentDir . "/plugins";

        foreach (Finder::findFiles("*.yml")->in($pluginDir) as $file) {

            $name = $file->getBasename(".yml");
            $dir = $file->getPath() . "/" . $name;
            $class = yaml_parse_file($dir . "/plugin.yml")["class"];

            require_once $dir . "/Plugin.php";
            $this->plugins[$name] = new $class(yaml_parse_file($file->getPathname()));
        }

        foreach ($this->plugins as $plugin) {

            try {
                $plugin->init($this);
            } catch (Exception\PluginException $e) {
                Debugger::log($e, "webby");
            }
        }
    }

    public function loadPageConfig($link)
    {
        // @todo caching
        $config = yaml_parse_file($this->pagesDir . "/" . Route::linkToPath($link) . ".yml");
        if (!empty($config["extends"])) {
            // @todo intelligent merge
            $config += $this->loadPageConfig($config["extends"]);
        }
        return $config;
    }

}