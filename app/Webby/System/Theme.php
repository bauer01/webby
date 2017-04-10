<?php

namespace Webby\System;


use Nette\InvalidArgumentException;
use Nette\Neon\Neon;

class Theme
{

    private $current;
    private $config = [];
    private $dir;
    private $layout;
    private $parent;

    public function __construct(array $config)
    {
        if (!empty($config["current"])) {

            $this->current = $config["current"];
            $this->layout = $config["layout"];
            $this->dir = $config["dir"] . "/" . $this->current;

            $this->config = $this->loadFile($this->dir . "/theme.neon");
            if (!empty($this->config["parent"])) {

                $this->parent = $this->config["parent"];

                if ($this->parent === $this->current) {
                    throw new InvalidArgumentException("Theme '" . $this->current . "' can not be parent for itself!");
                }

                if (!is_file($parentConfigPath = $this->dir . "/../" . $this->parent . "/theme.neon")) {
                    throw new InvalidArgumentException(
                        "Parent theme '" . $this->parent . "' defined in '" . $this->current . "' not found!"
                    );
                }

                $this->config = array_merge_recursive($this->config, $this->loadFile($parentConfigPath));
            }
        }
    }

    private function loadFile($path)
    {
        return Neon::decode(file_get_contents($path));
    }

    private function loadResource($resource, $name)
    {
        $dir = $this->dir;
        $path = "/" . $resource . "/" . $name . ".neon";
        if ($this->parent && !is_file($dir . $path)) {
            $dir = $this->dir . "/../" . $this->parent;
        }
        return $this->loadFile($dir . $path);
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
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
        return $this->loadResource("layouts", $this->layout);
    }


    public function getTemplate($name)
    {
        return $this->loadResource("templates", $name);
    }
    public function getStructure($name)
    {
        return $this->loadResource("structures", $name);
    }

    /**
     * @return array|mixed
     */
    public function getConfig()
    {
        return $this->config;
    }

}