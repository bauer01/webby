<?php

namespace Webby\System;


class Theme
{

    private $config = [];
    private $assetsDir;
    private $dir;

    public function __construct($dir, $theme, $layout, $assetsDir)
    {
        $this->dir = $dir;
        $this->config = yaml_parse_file($dir . "/theme.yml")["config"] + yaml_parse_file($dir . ".yml");
        $this->assetsDir = $assetsDir;
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