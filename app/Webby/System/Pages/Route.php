<?php

namespace Webby\System\Pages;

use Nette\Application\IRouter;
use Nette\Application\Request;
use Nette\Http\IRequest;
use Nette\Http\Url;
use Webby\Presenter\DefaultPresenter;
use Webby\System\Pages;

class Route implements IRouter
{

    private $pages;

    public function __construct(Pages $pages)
    {
        $this->pages = $pages;
    }

    public function match(IRequest $httpRequest)
    {
        $link = Route::pathToLink($httpRequest->getUrl()->getPath());

        if (empty($link)) {
            $link = $this->pages->getHomepage();
        }

        if ($link === $this->pages->getErrorPage()) {
            return;
        }

        if (is_file($this->pages->getDir() . "/" . self::linkToPath($link) . ".neon")) {

            return self::createRequest(
                $httpRequest,
                $link,
                function (DefaultPresenter $presenter) use ($link) {
                    return $this->pages->createPageResponse(
                        $presenter,
                        $link,
                        Pages::loadPageConfig($this->pages->getDir(), $link)
                    );
                }
            );
        }
    }

    public static function createRequest(\Nette\Http\Request $httpRequest, $link, callable $cb = null, $presenter = "Default")
    {
        return new Request(
            $presenter,
            $httpRequest->getMethod(),
            [
                "link" => $link,
                "parameters" => $httpRequest->getQuery(),
                "callback" => $cb
            ],
            $httpRequest->getPost(),
            $httpRequest->getFiles(),
            [Request::SECURED => $httpRequest->isSecured()]
        );
    }

    public function constructUrl(Request $appRequest, Url $refUrl)
    {
        $link = $appRequest->getParameter("link");

        if (is_file($this->pages->getDir() . "/" . self::linkToPath($link) . ".neon")) {

            $url = $refUrl->getBaseUrl();
            if ($link !== $this->pages->getHomepage()) {
                $url .= "/" . self::linkToPath($link);
            }
            if (!empty($parameters = $appRequest->getParameter("parameters"))) {
                $url .= "?" . http_build_query($parameters);
            }
            return $url;
        }
    }

    public static function linkToPath($link, $sep = "/")
    {
        return implode($sep, array_filter(explode(":", $link)));
    }

    public static function pathToLink($path)
    {
        return implode(":", array_filter(explode("/", $path)));
    }

}