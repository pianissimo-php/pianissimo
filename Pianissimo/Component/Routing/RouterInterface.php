<?php

namespace Pianissimo\Component\Routing;

interface RouterInterface
{
    public function matchRoute(string $path): ?Route;

    public function findRoute(string $routeName): ?Route;

    public function getRoute(string $routeName): Route;
}
