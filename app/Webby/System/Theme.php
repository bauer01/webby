<?php

namespace Webby\System;


use Nette\Neon\Neon;

class Theme
{

    private $config = [];
    private $assetsDir;
    private $dir;

    public function __construct(array $config)
    {
        $this->dir = $config["dir"] . "/" . $config['current'];
        $this->config = Neon::decode(file_get_contents($this->dir . "/theme.neon"))["config"]
            + Neon::decode(file_get_contents($this->dir . ".neon"));
        $this->assetsDir = $config["assetsDir"];
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
    public function getAssetsDir()
    {
        return $this->assetsDir;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    public function getLayout()
    {
        return $this->config["layouts"][$this->config["layout"]];
    }

}