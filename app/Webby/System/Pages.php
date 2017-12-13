<?php

namespace Webby\System;


use Nette\Application\IPresenter;
use Nette\Application\IRouter;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\Responses\TextResponse;
use Nette\Bridges\ApplicationLatte\ILatteFactory;
use Nette\DI\Container;
use Nette\Http\Request;
use Nette\Http\Url;
use Nette\Http\UrlScript;
use Nette\Neon\Neon;
use Nette\Utils\Finder;
use Webby\Exception\RedirectException;
use Webby\System\Pages\Route;

class Pages
{

    private $dir;
    private $homepage;
    private $language;
    private $charset;
    private $title;
    private $title_delimiter;
    private $description;
    private $theme;
    private $latteFactory;
    private $particles;
    private $container;
    private $errorPage;
    private $httpRequest;
    private $body;
    private $head;

    public function __construct(array $config, Container $container)
    {
        $this->dir = $config["dir"];
        $this->homepage = $config["homepage"];
        $this->errorPage = (string) $config["error"];
        $this->language = $config["language"];
        $this->charset = $config["charset"];
        $this->title = $config["title"];
        $this->title_delimiter = $config["title_delimiter"];
        $this->description = $config["description"];
        $this->body = $config["body"];
        $this->head = $config["head"];

        $this->particles = $container->getByType(Particles::class);
        $this->httpRequest = $container->getByType(Request::class);
        $this->theme = $container->getByType(Theme::class);
        $this->latteFactory = $container->getByType(ILatteFactory::class);
        $this->container = $container;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return mixed
     */
    public function getHead()
    {
        return $this->head;
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

    public function createPageResponse(IPresenter $presenter, $link, array $config)
    {
        $latte = $this->latteFactory->create();

        try {

            $response = $latte->renderToString(
                __DIR__ . "/Pages/layout.latte",
                $templateParameters = $this->getTemplateParameters($presenter, $link, $config)
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

    private function getTemplateParameters(IPresenter $presenter, $link, array $config)
    {
        $theme = isset($config["theme"][$this->theme->getCurrent()]) ? $config["theme"][$this->theme->getCurrent()] : [];
        $url = $this->httpRequest->getUrl();

        return [
            "presenter" => $presenter,
            "container" => $this->container,
            "baseUrl" => rtrim($url->getBaseUrl(), '/'),
            "basePath" => rtrim($url->getBasePath(), '/'),
            "webby" => (object)[
                "link" => $link,
                "title" => $config["title"],
                "content" => isset($theme["content"]) ? $theme["content"] : [],
                "template" => isset($theme["template"]) ? $this->theme->getTemplate($theme["template"]) : false,
                "templateDir" => __DIR__ . "/Pages",
                "parameters" => !empty($config["parameters"]) && is_array($config["parameters"]) ? $config["parameters"] : []
            ]
        ];
    }

    public static function createSitemapCb(Pages $pages, LinkGenerator $linkGenerator, IRouter $router)
    {
        return function (\samdark\sitemap\Sitemap $sitemap) use ($pages, $linkGenerator, $router) {

            $dir = realpath($pages->getDir());
            if (!$dir) {
                return;
            }

            foreach (Finder::findFiles('*.neon')->from($dir) as $file) {

                $relativePath = substr($file->getPath() . "/" . $file->getBasename('.neon'), strlen($dir) + 1);

                $url = new Url();
                $url->setPath($relativePath);
                $httpRequest = new Request(new UrlScript($url));

                if ($appRequest = $router->match($httpRequest)) {
                    $sitemap->addItem($linkGenerator->link($appRequest->getParameter("link")));
                }
            }
        };
    }
}