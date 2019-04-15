<?php

namespace Pianissimo\Component\Core;

use Pianissimo\Component\Container\Container;

class Core
{
    /** @var Container */
    protected $container;

    public function __construct()
    {
        $this->container = new Container();
    }
}