<?php

namespace Pianissimo\Component\Container;

use Pianissimo\Component\Routing\Route;

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

    public function get(string $name): ?Route
    {
        if (isset($this->registry[$name]) === true) {
            return $this->registry[$name];
        }

        return null;
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