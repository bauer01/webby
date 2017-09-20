<?php

namespace Webby\Presenter;

use Nette\Application\BadRequestException;
use Nette\Application\IPresenter;
use Nette\Application\Request;
use Nette\DI\Container;
use Nette\Http\Session;
use Nette\Utils\Random;
use Webby\Exception\RedirectException;
use Webby\System\Pages;
use Webby\System\Particles;

class DefaultPresenter implements IPresenter
{

    /** @var Request */
    private $appRequest;
    private $session;
    private $storedLink;
    private $container;

    public function __construct(Container $container, Session $session)
    {
        $this->container = $container;
        $this->session = $session->getSection("webby");
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
            "link" => $this->appRequest->getParameter("link"),
            "parameters" => $this->appRequest->getParameter("parameters")
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

    public function run(Request $appRequest)
    {
        $this->appRequest = $appRequest;

        return call_user_func(
            $appRequest->getParameter("callback"),
            $this,
            $this->container->getByType(Pages::class),
            $appRequest->getParameter("link"),
            $appRequest->getParameter("parameters")
        );
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
        return $this->container->getByType(\Nette\Http\Request::class)->isAjax();
    }

    public function invalidate($particle)
    {
        return $this->container->getByType(Particles::class)->invalidate($particle);
    }

    /**
     * @return Request
     */
    public function getAppRequest()
    {
        return $this->appRequest;
    }

}