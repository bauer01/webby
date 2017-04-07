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
use Nette\Utils\Random;
use Webby\Exception\RedirectException;
use Webby\System\Particles;
use Webby\System\Pages;

class DefaultPresenter implements IPresenter
{

    private $latte;
    private $router;
    /** @var Request */
    private $request;
    private $session;
    private $storedLink;
    private $container;
    private $pages;
    private $particles;

    public function __construct(Container $container, Pages $pages, Particles $particles, ILatteFactory $latteFactory, IRouter $router, Session $session)
    {
        $this->container = $container;
        $this->latte = $latteFactory->create();
        $this->router = $router;
        $this->session = $session->getSection("webby");
        $this->pages = $pages;
        $this->particles = $particles;
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
            "parameters" => $this->request->getParameter("parameters")
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
        $this->redirect($link["link"], $link["parameters"]);
    }

    public function link($link, $args = [])
    {
        return $this->pages->link($link, $args); // @todo move to pages?
    }

    public function run(Request $appRequest)
    {
        $this->request = $appRequest;

        return call_user_func(
            $this->request->getParameter("callback"),
            $this,
            $this->request->getParameter("link")
        );
    }

    public function createPageResponse($link, array $pageConfig)
    {
        try {

            $response = $this->latte->renderToString(
                __DIR__ . "/layout.latte",
                $templateParameters = $this->getTemplateParameters($link, $pageConfig)
            );
        } catch (RedirectException $e) {
            return $this->createRedirectResponse($e->getLink(), $e->getParameters());
        }

        if ($this->isAjax()) {

            $response = [];
            foreach ($this->particles->getAdded() as $id => $particle) {

                $response[$id] = $this->latte->renderToString(
                    $this->particles->getTemplatePath($particle["particle"]),
                    $templateParameters + $particle
                );
            }
            return new JsonResponse($response);
        }
        return new TextResponse($response);
    }

    public function createRedirectResponse($link, array $parameters = [])
    {
        return new RedirectResponse($this->pages->link($link, $parameters));
    }

    private function getTemplateParameters($link, array $pageConfig)
    {
        $url = $this->request->getParameter("url");
        return [
            "presenter" => $this,
            "container" => $this->container,
            "baseUrl" => rtrim($url->getBaseUrl(), '/'),
            "basePath" => rtrim($url->getBasePath(), '/'),
            "webby" => (object) [
                "link" => $link,
                "page" => $pageConfig,
                "templateDir" => __DIR__
            ]
        ];
    }

    /**
     * @return Particles
     */
    public function getParticles()
    {
        return $this->particles;
    }

    public function redirect($link, array $parameters = [], $code = \Nette\Http\IResponse::S302_FOUND)
    {
        throw new RedirectException($link, $parameters, $code);
    }

    public function error($message = null, $httpCode = \Nette\Http\IResponse::S404_NOT_FOUND)
    {
        throw new BadRequestException((string) $message, (int) $httpCode);
    }

    public function isAjax()
    {
        return (bool) $this->request->getParameter("ajax");
    }
}