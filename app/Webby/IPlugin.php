<?php

namespace Webby;

interface IPlugin
{

    public function __construct(array $config);

    public function init(System $system);

}