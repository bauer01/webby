<?php

namespace Webby\System;


class Sitemap
{

    private $enabled;
    private $callbacks = [];

    public function __construct(array $config)
    {
        $this->enabled = (bool) $config["enabled"];
    }

    public function registerCallback(callable $cb)
    {
        $this->callbacks[] = $cb;
    }

    public function getFileName()
    {
        return 'sitemap.xml';
    }

    /**
     * @return mixed
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    public function dump()
    {
        $sitemap = new \samdark\sitemap\Sitemap(WWW_DIR . "/" . $this->getFileName());
        foreach ($this->callbacks as $cb) {
            call_user_func($cb, $sitemap);
        }
        $sitemap->write();
    }

}