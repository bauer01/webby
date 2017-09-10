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

        $dir = realpath($this->pages->getDir());
        if (!$dir) {
            return false;
        }

        foreach (Finder::findFiles('*.neon')->from($dir) as $file) {

            $relativePath = ltrim($file->getPath() . "/" . $file->getBasename('.neon'), $dir);

            $sitemap->addItem(
                $this->pages->link(
                    Route::pathToLink($relativePath)
                )
            );
        }

        $sitemap->write();
    }

}