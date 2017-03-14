<?php

namespace Webby\System;


use Nette\InvalidArgumentException;

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
        if (!isset($this->items[$key])) {
            throw new InvalidArgumentException("Menu '" . $key . "' not found!");
        }
        return $this->items[$key];
    }

}