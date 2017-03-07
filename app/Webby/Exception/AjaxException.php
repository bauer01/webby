<?php

namespace Webby\Exception;

class AjaxException extends \Webby\Exception
{

    private $link;
    private $particles;
    private $parameters;

    public function __construct($link, array $particles = [], array $parameters = [])
    {
        $this->link = $link;
        $this->particles = $particles;
        $this->parameters = $parameters;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function getLink()
    {
        return $this->link;
    }

    public function getParticles()
    {
        return $this->particles;
    }

}