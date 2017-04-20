<?php

namespace Webby\System;


use Nette\Http\Request;

class Robots
{

    private $enabled;
    private $disallowed = [];
    private $allowed = [];
    private $request;
    private $sitemap;

    public function __construct(array $config, Request $request, Sitemap $sitemap)
    {
        $this->request = $request;
        $this->sitemap = $config["sitemap"] ? $sitemap : false;
        $this->enabled = $config["enabled"];
        $this->disallowed = $config["disallow"];
        $this->allowed = $config["allow"];
    }

    /**
     * @return mixed
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @return mixed
     */
    public function getDisallowed()
    {
        return $this->disallowed;
    }

    public function dump()
    {
        $content = "";

        if ($this->sitemap) {
            $content .= "Sitemap: " . $this->request->getUrl()->getBaseUrl() . "/" . $this->sitemap->getFileName() . PHP_EOL . PHP_EOL;
        }

        if ($this->disallowed || $this->allowed) {

            $content .= 'User-agent: *' . PHP_EOL;
            foreach ($this->disallowed as $disallowed) {
                $content .= "Disallow: " . $disallowed . PHP_EOL;
            }
            foreach ($this->allowed as $allowed) {
                $content .= "Allow: " . $allowed . PHP_EOL;
            }
        }

        file_put_contents(WWW_DIR . "/robots.txt", $content);
    }

}