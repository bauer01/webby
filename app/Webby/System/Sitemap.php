<?php

namespace Webby\System;


use Nette\Utils\Finder;
use Webby\Routing\Route;

class Sitemap
{

    private $enabled;
    private $pages;

    public function __construct(array $config, Pages $pages)
    {
        $this->enabled = $config["enabled"];
        $this->pages = $pages;
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
        $sitemap = new \samdark\sitemap\Sitemap(__DIR__ . '/sitemap.xml');
        foreach (Finder::findFiles('*.neon')->from($this->pages->getDir()) as $file) {
            $sitemap->addItem($this->pages->link(Route::pathToLink($file->getBasename('.neon'))));
        }
        $sitemap->write();
    }

}