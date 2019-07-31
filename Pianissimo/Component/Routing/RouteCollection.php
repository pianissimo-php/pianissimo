<?php

namespace Pianissimo\Component\Routing;

use ArrayIterator;
use IteratorAggregate;
use Pianissimo\Component\Routing\Exception\RouteNotFoundException;

class RouteCollection implements IteratorAggregate
{
    /**
     * @var Route[]|array
     */
    private $routes;

    public function __construct()
    {
        $this->routes = [];
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->routes);
    }

    public function find(string $name): ?Route
    {
        if (isset($this->routes[$name]) === true) {
            return $this->routes[$name];
        }

        return null;
    }

    public function get(string $name): Route
    {
        $route = $this->find($name);
        if ($route === null) {
            throw new RouteNotFoundException('Route not found');
        }

        return $route;
    }

    public function add(Route $route): self
    {
        $this->routes[$route->getName()] = $route;

        return $this;
    }

    public function all(): array
    {
        return $this->routes;
    }

    public function getIterator() {
        return new ArrayIterator($this->routes);
    }
}
