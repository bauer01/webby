<?php

namespace Webby\Model\Adapter;

use UniMapper\Adapter\IQuery;

class Query implements IQuery
{

    private $cb;

    public function __construct(callable $cb)
    {
        $this->cb = $cb;
    }

    public function setFilter(array $filter)
    {

    }

    public function setAssociations(array $associations)
    {

    }

    public function getRaw()
    {
        return ($this->cb)();
    }

}