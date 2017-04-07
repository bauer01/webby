<?php

namespace Webby\System;


use Nette\Application\IRouter;
use Nette\Http\Request;
use Nette\Http\UrlScript;
use Nette\Neon\Neon;
use Webby\Presenter\DefaultPresenter;
use Webby\Routing\Route;

class Pages
{

    private $dir;
    private $homepage;
    private $language;
    private $charset;
    private $title;
    private $title_delimiter;
    private $description;
    private $router;

    public function __construct(array $config, Request $request, IRouter $router)
    {
        $this->dir = $config["dir"];
        $this->homepage = $config["homepage"];
        $this->language = $config["language"];
        $this->charset = $config["charset"];
        $this->title = $config["title"];
        $this->title_delimiter = $config["title_delimiter"];
        $this->description = $config["description"];
        $this->request = $request;
        $this->router = $router;
    }

    /**
     * @return mixed
     */
    public function getDir()
    {
        return $this->dir;
    }

    /**
     * @return mixed
     */
    public function getHomepage()
    {
        return $this->homepage;
    }

    /**
     * @return mixed
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @return mixed
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return mixed
     */
    public function getTitleDelimiter()
    {
        return $this->title_delimiter;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    public static function createRoute(array $pagesConfig)
    {
        return new Route(function($link) use ($pagesConfig) {

            if (!$link) {
                $link = $pagesConfig["homepage"];
            }

            if (is_file($pagesConfig['dir'] . "/" . Route::linkToPath($link) . ".neon")) {
                $pageConfig = self::loadPageConfig($pagesConfig["dir"], $link);
                return [
                    "callback" => function (DefaultPresenter $presenter) use ($link, $pageConfig) {
                        return $presenter->createPageResponse($link, $pageConfig);
                    },
                    "pageConfig" => $pageConfig
                ];
            }
            return false;
        });
    }

    public static function loadPageConfig($dir, $link)
    {
        // @todo caching
        $config = Neon::decode(file_get_contents($dir . "/" . Route::linkToPath($link) . ".neon"));
        if (!empty($config["extends"])) {
            // @todo intelligent merge
            $config += self::loadPageConfig($dir, $config["extends"]);
        }
        return $config;
    }

    public function link($link, array $args = [])
    {
        if ($link === $this->getHomepage()) {
            return $this->request->getUrl()->getBaseUrl();
        }

        $url = clone $this->request->getUrl();
        $url->setQuery($args);
        return $this->router->constructUrl(Route::createRequest($this->request, $link), $url);
    }

    public function linkToRequest($link, array $parameters = [])
    {
        $urlScript = new UrlScript($this->link($link, $parameters));
        return $this->router->match(new Request($urlScript));
    }

}