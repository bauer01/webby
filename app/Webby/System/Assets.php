<?php

namespace Webby\System;


use Assetic\Asset\AssetCollection;

class Assets
{

    private $dir;
    private $mediaDir;
    private $js;
    private $css;

    public function __construct(array $config)
    {
        $this->dir = $config["dir"];

        $this->js = new AssetCollection();
        $this->js->setTargetPath("scripts.js");

        $this->css = new AssetCollection();
        $this->css->setTargetPath("styles.css");

        $this->mediaDir = $config["mediaDir"];
    }

    /**
     * @return AssetCollection
     */
    public function getJs()
    {
        return $this->js;
    }

    /**
     * @return AssetCollection
     */
    public function getCss()
    {
        return $this->css;
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
    public function getMediaDir()
    {
        return $this->mediaDir;
    }

}