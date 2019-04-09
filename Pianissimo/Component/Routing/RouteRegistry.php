<?php

namespace Pianissimo\Component\Routing;

use Pianissimo\Component\HttpFoundation\Exception\NotFoundHttpException;
use Pianissimo\Component\RegistryInterface;

class RouteRegistry implements RegistryInterface
{
    /** @var array */
    private $registry;

    public function __construct()
    {
        $this->registry = [];
    }

    public function initialize(array $routes): void
    {
        $this->registry = $routes;
    }

    public function find(string $name): ?Route
    {
        if (isset($this->registry[$name]) === true) {
            return $this->registry[$name];
        }

        return null;
    }

    public function get(string $name): Route
    {
        $route = $this->find($name);
        if ($route === null) {
            throw new NotFoundHttpException('Route not found');
        }

        return $route;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->registry);
    }

    public function all(): array
    {
        return $this->registry;
    }
}