<?php

namespace Webby\System;


use Nette\Application\IPresenter;
use Nette\Application\IRouter;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\Responses\RedirectResponse;
use Nette\Application\Responses\TextResponse;
use Nette\Bridges\ApplicationLatte\ILatteFactory;
use Nette\DI\Container;
use Nette\Http\Request;
use Nette\Http\UrlScript;
use Nette\Neon\Neon;
use Webby\Exception\RedirectException;
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
    private $theme;
    private $latteFactory;
    private $particles;
    private $container;
    private $errorPage;
    private $httpRequest;

    public function __construct(array $config, Container $container)
    {
        $this->dir = $config["dir"];
        $this->homepage = $config["homepage"];
        $this->errorPage = $config["error"];
        $this->language = $config["language"];
        $this->charset = $config["charset"];
        $this->title = $config["title"];
        $this->title_delimiter = $config["title_delimiter"];
        $this->description = $config["description"];

        $this->particles = $container->getByType(Particles::class);
        $this->httpRequest = $container->getByType(Request::class);
        $this->router = $container->getByType(IRouter::class);
        $this->theme = $container->getByType(Theme::class);
        $this->latteFactory = $container->getByType(ILatteFactory::class);
        $this->container = $container;
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
    public function getErrorPage()
    {
        return $this->errorPage;
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
                    "callback" => function (DefaultPresenter $presenter, Pages $pages) use ($link, $pageConfig) {
                        return $pages->createPageResponse($presenter, $link, $pageConfig);
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
            return $this->httpRequest->getUrl()->getBaseUrl();
        }

        $url = clone $this->httpRequest->getUrl();
        $url->setQuery($args);
        return $this->router->constructUrl(Route::createRequest($this->httpRequest, $link), $url);
    }

    public function linkToRequest($link, array $parameters = [])
    {
        $urlScript = new UrlScript($this->link($link, $parameters));
        return $this->router->match(new Request($urlScript));
    }

    public function createPageResponse(IPresenter $presenter, $link, array $pageConfig)
    {
        $latte = $this->latteFactory->create();

        try {

            $response = $latte->renderToString(
                __DIR__ . "/Pages/layout.latte",
                $templateParameters = $this->getTemplateParameters($presenter, $link, $pageConfig)
            );
        } catch (RedirectException $e) {
            return $this->createRedirectResponse($e->getLink(), $e->getParameters());
        }

        if ($this->httpRequest->isAjax()) {

            $response = [];
            foreach ($this->particles->getAdded() as $id => $particle) {

                if (in_array($particle["particle"], $this->particles->getInvalidated())) {

                    $response[$id] = $latte->renderToString(
                        $this->particles->getTemplatePath($particle["particle"]),
                        $templateParameters + $particle
                    );
                }
            }
            return new JsonResponse($response);
        }
        return new TextResponse($response);
    }

    public function createRedirectResponse($link, array $parameters = [])
    {
        return new RedirectResponse($this->link($link, $parameters));
    }

    private function getTemplateParameters(IPresenter $presenter, $link, array $pageConfig)
    {
        $theme = isset($pageConfig["theme"][$this->theme->getCurrent()]) ? $pageConfig["theme"][$this->theme->getCurrent()] : [];
        $url = $this->httpRequest->getUrl();

        return [
            "presenter" => $presenter,
            "container" => $this->container,
            "baseUrl" => rtrim($url->getBaseUrl(), '/'),
            "basePath" => rtrim($url->getBasePath(), '/'),
            "webby" => (object) [
                "link" => $link,
                "title" => $pageConfig["title"],
                "content" => isset($theme["content"]) ? $theme["content"] : [],
                "template" => isset($theme["template"]) ? $this->theme->getTemplate($theme["template"]) : [],
                "templateDir" => __DIR__ . "/Pages"
            ]
        ];
    }

}