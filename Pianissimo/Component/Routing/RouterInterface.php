<?php

namespace Pianissimo\Component\Routing;

use InvalidArgumentException;
use Pianissimo\Component\Routing\Exception\RouteNotFoundException;

interface RouterInterface
{
    public function matchRoute(string $path): ?Route;

    public function findRoute(string $routeName): ?Route;

    /**
     * @throws RouteNotFoundException
     */
    public function getRoute(string $routeName): Route;

    /**
     * @throws RouteNotFoundException
     * @throws InvalidArgumentException
     */
    public function generateUrl(string $routeName, array $parameters): string;
}
