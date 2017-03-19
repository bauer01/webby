<?php

namespace Webby\System;


use Nette\Neon\Neon;

class Theme
{

    private $current;
    private $config = [];
    private $dir;
    private $layout;

    public function __construct(array $config)
    {
        $this->current = $config["current"];
        $this->layout = $config["layout"];
        $this->dir = $config["dir"] . "/" . $this->current;
        $this->config = Neon::decode(file_get_contents($this->dir . "/theme.neon"));
    }

    /**
     * @return mixed
     */
    public function getCurrent()
    {
        return $this->current;
    }

    /**
     * @return mixed
     */
    public function getDir()
    {
        return $this->dir;
    }

    public function getLayout()
    {
        return Neon::decode(file_get_contents($this->dir . "/layouts/" . $this->layout . ".neon"));
    }

    public function getPage($name)
    {
        return Neon::decode(file_get_contents($this->dir . "/pages/" . $name . ".neon"));
    }

    public function getStructure($name)
    {
        return Neon::decode(file_get_contents($this->dir . "/structures/" . $name . ".neon"));
    }

    /**
     * @return array|mixed
     */
    public function getConfig()
    {
        return $this->config;
    }

}