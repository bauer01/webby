<?php

namespace Webby\System;


class Menus
{

    private $items = [];

    public function __construct($items)
    {
        $this->items = $items;
    }

    /**
     * @return array
     */
    public function getMenu($key)
    {
        return $this->items[$key];
    }

}