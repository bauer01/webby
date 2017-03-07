<?php

namespace Webby\Exception;

use Nette\Http\IResponse;

class RedirectException extends \Webby\Exception
{
    private $link;
    private $parameters;

    public function __construct($link, array $parameters = [], $code = IResponse::S302_FOUND)
    {
        parent::__construct("Redirect", $code);
        $this->link = $link;
        $this->parameters = $parameters;
    }

    public function getLink()
    {
        return $this->link;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

}