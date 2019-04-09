<?php

namespace Pianissimo\Component\Routing;

interface RouteLoaderInterface
{
    public function load(): array;
}