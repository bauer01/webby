<?php

namespace Webby\System;


class Particles
{

    private $invalidated = [];
    private $added = [];
    private $theme;
    private $definitions;

    public function __construct(array $definitions, Theme $theme)
    {
        $this->theme = $theme;
        $this->definitions = $definitions;
    }

    /**
     * @return array
     */
    public function getInvalidated()
    {
        return $this->invalidated;
    }

    /**
     * @return array
     */
    public function getAdded()
    {
        return $this->added;
    }

    /**
     * @return array
     */
    public function getDefinitions()
    {
        return $this->definitions;
    }

    public function invalidate($particles)
    {
        if (is_array($particles)) {
            $this->invalidated = array_unique(array_merge($this->invalidated, $particles));
        } else {
            $this->invalidated[] = $particles;
        }
    }

    public function add(array $config)
    {
        $id = implode("-", ["particle", $config["particle"], (string) (count($this->added) + 1)]);
        $this->added[$id] = $config;
        return $id;
    }

    public function getTemplatePath($particle)
    {
        $path = "/particles/" . $particle . ".latte";
        $themeDir = $this->theme->getDir();
        $parentThemeDir = $themeDir . "/../" . $this->theme->getParent();

        if (is_file($themeDir . $path)) {
            return $themeDir . $path;
        } else if (is_file($parentThemeDir . $path)) {
            return $parentThemeDir . $path;
        } else if (is_file(__DIR__ . $path)) {
            return __DIR__ . $path;
        }
        throw new \Exception("Template for particle '" . $particle . "' not found!");
    }

}