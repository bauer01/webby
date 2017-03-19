<?php

namespace Webby\System;


use Nette\InvalidArgumentException;

class Particles
{

    private $dir;
    private $theme;

    public function __construct($dir, Theme $theme)
    {
        $this->dir = $dir;
        $this->theme = $theme;
    }

    public function getParticleTemplate($particle)
    {
        $parts = explode(':', $particle, 2);
        if (count($parts) <> 2) {
            throw new InvalidArgumentException("Invalid particle definition '" . $particle . "'!");
        }

        switch ($parts[0]) {
            case "system":
                $path = $this->dir;
                break;
            case "theme":
                $path = $this->theme->getDir() . "/particles";
                break;
            default:
                throw new InvalidArgumentException("Unexpected particle type in '" . $particle . "'!");
        }

        return $path . "/" . $parts[1] . ".latte";
    }

}