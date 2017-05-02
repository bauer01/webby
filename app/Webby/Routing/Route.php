<?php

namespace Webby\Routing;

use Nette\Application\IRouter;
use Nette\Application\Request;
use Nette\Application\Routers\RouteList;
use Nette\Http\IRequest;
use Nette\Http\Url;

class Route implements IRouter
{

    private $matchCb;

    public function __construct(callable $matchCb)
    {
        $this->matchCb = $matchCb;
    }

    /**
     * @return RouteList
     */
    public static function createRouter()
    {
        return new RouteList();
    }

    public function match(IRequest $request)
    {
        $link = self::pathToLink($request->getUrl()->getPath());
        if ($config = call_user_func($this->matchCb, $link, $request)) {
            return self::createRequest($request, $link, $config);
        }
    }

    public static function createRequest(\Nette\Http\Request $httpRequest, $link, array $config = [])
    {
        return new Request(
            "Default",
            $httpRequest->getMethod(),
            [
                "link" => $link,
                "parameters" => $httpRequest->getQuery()
            ] + $config,
            $httpRequest->getPost(),
            $httpRequest->getFiles(),
            [Request::SECURED => $httpRequest->isSecured()]
        );
    }

    public function constructUrl(Request $appRequest, Url $refUrl)
    {
        $url = $refUrl->getHostUrl() . "/" . self::linkToPath($appRequest->getParameter("link"));
        if ($query = $refUrl->getQuery()) {
            $url .= "?" . $query;
        }
        return $url;
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