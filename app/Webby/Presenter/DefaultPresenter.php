<?php

namespace Webby\Presenter;

use Nette\Application\BadRequestException;
use Nette\Application\IPresenter;
use Nette\Application\IRouter;
use Nette\Application\Request;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\Responses\RedirectResponse;
use Nette\Application\Responses\TextResponse;
use Nette\Bridges\ApplicationLatte\ILatteFactory;
use Nette\DI\Container;
use Nette\Http\Session;
use Nette\Http\UrlScript;
use Nette\Utils\Random;
use Webby\Exception\AjaxException;
use Webby\Exception\RedirectException;
use Webby\Routing\Route;
use Webby\System;

class DefaultPresenter implements IPresenter
{

    private $httpRequest;
    private $latte;
    private $system;
    private $router;
    /** @var Request */
    private $request;
    private $session;
    private $storedLink;
    private $container;

    public function __construct(Container $container, \Nette\Http\Request $httpRequest, ILatteFactory $latteFactory, System $system, IRouter $router, Session $session)
    {
        $this->container = $container;
        $this->httpRequest = $httpRequest;
        $this->latte = $latteFactory->create();
        $this->system = $system;
        $this->router = $router;
        $this->session = $session->getSection("webby");
    }

    public function getSystem()
    {
        return $this->system;
    }

    /**
     * @return \Nette\Http\Request
     */
    public function getHttpRequest()
    {
        return $this->httpRequest;
    }

    public function storeLink($expiration = '+ 10 minutes')
    {
        $link = $this->storedLink;
        if ($link) {
            return $link;
        }

        do {
            $link = $this->storedLink = Random::generate(5);
        } while (isset($this->session[$this->storedLink]));
        $this->session[$link] = [
            "link" => $this->request->getParameter("link"),
            "parameters" => $this->httpRequest->getQuery()
        ];
        $this->session->setExpiration($expiration, $link);
        return $link;
    }

    public function restoreLink($key)
    {
        if (!isset($this->session[$key])) {
            return;
        }
        $link = $this->session[$key];
        unset($this->session[$key]);
        $this->sendRedirect($link["link"], $link["parameters"]);
    }

    public function link($link, $args = [])
    {
        if ($link === $this->system->getConfig("homepage")) {
            return $this->httpRequest->getUrl()->getBaseUrl();
        }

        $url = clone $this->httpRequest->getUrl();
        $url->setQuery($args);
        return $this->router->constructUrl(Route::createRequest($this->httpRequest, $link), $url);
    }

    public function run(Request $appRequest)
    {
        $this->request = $appRequest;

        try {

            return call_user_func(
                $this->request->getParameter("callback"),
                $this,
                $this->request->getParameter("link")
            );
        } catch (RedirectException $e) {
            return $this->createRedirectResponse($e->getLink(), $e->getParameters());
        } catch (AjaxException $e) {
            return $this->createAjaxResponse($e->getLink(), $e->getParticles(), $e->getParameters());
        }
    }

    public function createPageResponse($link, array $pageConfig)
    {
        return new TextResponse(
            $this->latte->renderToString(
                __DIR__ . "/layout.latte",
                $this->getTemplateParameters($link, $pageConfig)
            )
        );
    }

    private function linkToRequest($link, array $parameters = [])
    {
        $urlScript = new UrlScript($this->link($link, $parameters));
        return $this->router->match(new \Nette\Http\Request($urlScript));
    }

    public function createAjaxResponse($link, array $particles, array $parameters = [])
    {
        $appRequest = $this->linkToRequest($link, $parameters);
        if (!$appRequest) {
            $this->error("Page " . $link . " not found!");
        }
        $appParameters = $appRequest->getParameters();
        return new JsonResponse(
            $this->getParticles(
                $appParameters["pageConfig"],
                $particles,
                $this->getTemplateParameters(
                    $appParameters["link"],
                    $appParameters["pageConfig"]
                )
            )
        );
    }

    public function createRedirectResponse($link, array $parameters = [])
    {
        return new RedirectResponse($this->link($link, $parameters));
    }

    private function getTemplateParameters($link, array $pageConfig)
    {
        $url = $this->httpRequest->getUrl();
        return [
            "presenter" => $this,
            "baseUrl" => rtrim($url->getBaseUrl(), '/'),
            "basePath" => rtrim($url->getBasePath(), '/'),
            "webby" => (object) [
                "link" => $link,
                "layout" => $this->system->getLayout(),
                "page" => $pageConfig,
                "system" => $this->system,
                "templateDir" => __DIR__
            ]
        ];
    }

    private function getParticles(array $pageConfig, array $particles, array $templateParameters)
    {
        $layout = $templateParameters["webby"]->layout;

        $result = [];
        foreach ($layout["sections"] as $key => $section) {
            $lsectionId = "section" . $key;
            foreach ($section["rows"] as $key => $row) {
                $lrowId = $lsectionId . "-row" . $key;
                foreach ($row["columns"] as $key => $column) {
                    $lcolumnId = $lrowId . "-column" . $key;
                    foreach ($column["particles"] as $key => $particle) {
                        $lparticleId = $lcolumnId . "-particle" . $key . "-" . $particle["particle"];
                        if (in_array($particle["particle"], $particles)) {
                            $result[$lparticleId] = $this->latte->renderToString(
                                $this->system->getThemeDir() . "/particles/" . $particle['particle'] . ".latte",
                                $templateParameters + [
                                    "options" => empty($particle['options']) ?: (object) $particle['options']
                                ]
                            );
                        } elseif ($particle["particle"] === "page") {

                            foreach ($pageConfig["sections"] as $key => $section) {
                                $sectionId = $lparticleId . "-section" . $key;
                                foreach ($section["rows"] as $key => $row) {
                                    $rowId = $sectionId . "-row" . $key;
                                    foreach ($row["columns"] as $key => $column) {
                                        $columnId = $rowId . "-column" . $key;
                                        foreach ($column["particles"] as $key => $particle) {
                                            $particleId = $columnId . "-particle" . $key . "-" . $particle["particle"];
                                            if (in_array($particle["particle"], $particles)) {
                                                $result[$particleId] = $this->latte->renderToString(
                                                    $this->system->getThemeDir() . "/particles/" . $particle['particle'] . ".latte",
                                                    $templateParameters + [
                                                        "options" => empty($particle['options']) ?: (object) $particle['options']
                                                    ]
                                                );
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $result;
    }

    public function sendAjax($link, array $particles, array $parameters = [])
    {
        throw new AjaxException($link, $particles, $parameters);
    }

    public function isAjax()
    {
        return $this->httpRequest->isAjax();
    }

    public function sendRedirect($link, array $parameters = [], $code = \Nette\Http\IResponse::S302_FOUND)
    {
        throw new RedirectException($link, $parameters, $code);
    }

    public function error($message = null, $httpCode = \Nette\Http\IResponse::S404_NOT_FOUND)
    {
        throw new BadRequestException((string) $message, (int) $httpCode);
    }

}