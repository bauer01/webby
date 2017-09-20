<?php

namespace Webby\System;


use Nette\Application\IRouter;
use Nette\Http\Request;
use Webby\System\Pages\Route;

class LinkGenerator
{

    private $router;
    private $httpRequest;

    public function __construct(IRouter $router, Request $httpRequest)
    {
        $this->router = $router;
        $this->httpRequest = $httpRequest;
    }

    public function link($link, array $parameters = [])
    {
        if ($fragmentPos = strpos($link, "#" )) {
            $fragment = substr($link, $fragmentPos);
            $link = substr($link, 0, $fragmentPos);
 		} else {
            $fragment = '';
        }

        $appRequest = Route::createRequest($this->httpRequest, $link, null);
        $appRequest->setParameters(array_merge($appRequest->getParameters(), ["parameters" => $parameters]));
        return $this->router->constructUrl($appRequest, $this->httpRequest->getUrl()) . $fragment;
    }

}