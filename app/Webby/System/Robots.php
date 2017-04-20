<?php

namespace Webby\System;


class Robots
{

    private $enabled;
    private $disallowed;

    public function __construct(array $config)
    {
        $this->enabled = $config["enabled"];
        $this->disallowed = $config["disallowed"];
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
        $content = 'User-agent: *\n';
        foreach ($this->disallowed as $disallowed) {
            $content .= "Disallow: " . $disallowed . '\n';
        }
        file_put_contents(WWW_DIR . "/robots.txt", $content);
    }

}