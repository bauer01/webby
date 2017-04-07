<?php

namespace Webby\System;


class Particles
{

    private $invalidated = [];
    private $added = [];
    private $theme;

    public function __construct(Theme $theme)
    {
        $this->theme = $theme;
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

    public function invalidate($particle)
    {
        $this->invalidated[] = $particle;
    }

    public function add(array $config)
    {
        $id = count($this->added);
        $id = "particle-" . str_replace(":", "-", $config["particle"]) . "-" . (string) ($id + 1);
        $this->added[$id] = $config;
        return $id;
    }

    public function getTemplatePath($particle)
    {
        $parts = explode(':', $particle, 2);
        if (count($parts) <> 2) {
            throw new \InvalidArgumentException("Invalid particle definition '" . $particle . "'!");
        }

        $path = "/particles/" . $parts[1] . ".latte";
        switch ($parts[0]) {
            case "system":
                $dir = __DIR__ . "/..";
                break;
            case "theme":

                $dir = $this->theme->getDir();
                if ($this->theme->getParent() && !is_file($dir . $path)) {
                    $dir .= "/../" . $this->theme->getParent();
                }
                break;
            default:
                throw new \InvalidArgumentException("Unexpected particle type in '" . $particle . "'!");
        }

        return $dir . $path;
    }

}