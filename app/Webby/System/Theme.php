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
        $this->current = $config["current"];
        $this->layout = $config["layout"];
        $this->dir = $config["dir"] . "/" . $this->current;

        $config = $this->loadFile($this->dir . "/theme.neon");
        if (!empty($config["parent"])) {

            $this->parent = $config["parent"];

            if ($this->parent === $this->current) {
                throw new InvalidArgumentException("Theme '" . $this->current . "' can not be parent for itself!");
            }

            if (!is_file($parentConfig = $this->dir . "/../" . $this->parent . "/theme.neon")) {
                throw new InvalidArgumentException(
                    "Parent theme '" . $this->parent . "' defined in '" . $this->current . "' not found!"
                );
            }

            $this->config = array_merge_recursive($config, $this->loadFile($parentConfig));
        }
        $this->config = $config;
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

    public function getPage($name)
    {
        return $this->loadResource("pages", $name);
    }

    public function getStructure($name)
    {
        return $this->loadResource("structures", $name);
    }

    public function getParticle($name)
    {
        $parts = explode(':', $name, 2);
        if (count($parts) <> 2) {
            throw new InvalidArgumentException("Invalid particle definition '" . $name . "'!");
        }

        $path = "/particles/" . $parts[1] . ".latte";
        switch ($parts[0]) {
            case "system":
                $dir = __DIR__ . "/..";
                break;
            case "theme":

                $dir = $this->dir;
                if ($this->parent && !is_file($this->dir . $path)) {
                     $dir .= "/../" . $this->parent;
                }
                break;
            default:
                throw new InvalidArgumentException("Unexpected particle type in '" . $name . "'!");
        }

        return $dir . $path;
    }

    /**
     * @return array|mixed
     */
    public function getConfig()
    {
        return $this->config;
    }

}