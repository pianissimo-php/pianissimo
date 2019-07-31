<?php

namespace Pianissimo\Component\Routing;

use Pianissimo\Component\Routing\Exception\RouteNotFoundException;

class Router implements RouterInterface
{
    /**
     * @var Route[]|RouteCollection
     */
    protected $routeCollection;

    /**
     * @var RouteLoaderInterface[]|array
     */
    private $routeLoaders;

    public function __construct()
    {
        $this->routeCollection = new RouteCollection();
        $this->routeLoaders = [];
    }

    /**
     * Returns the initialized routes.
     * @return Route[]|array
     */
    public function getRoutes(): array
    {
        return $this->routeCollection->all();
    }

    /**
     * Returns the Route instance whose paths match or returns null if there are no matches.
     */
    public function matchRoute(string $path): ?Route
    {
        $routes = $this->getRoutes();

        /** @var Route $route */
        foreach ($routes as $route) {
            if ($route->getPath() === $path) {
                return $route;
            }
        }

        return null;
    }

    /**
     * Returns the Route instance whose names match or returns null if there are no matches.
     */
    public function findRoute(string $routeName): ?Route
    {
        $routes = $this->getRoutes();

        foreach ($routes as $route) {
            if ($route->getName() === $routeName) {
                return $route;
            }
        }

        return null;
    }

    /**
     * Returns the Route instance whose names match or throws an exception if there are no matches.
     * @throws RouteNotFoundException
     */
    public function getRoute(string $routeName): Route
    {
        $route = $this->findRoute($routeName);

        if ($route === null) {
            throw new RouteNotFoundException(sprintf("Route with name '%s' not found", $routeName));
        }

        return $route;
    }

    public function addLoader(RouteLoaderInterface $routeLoader): self
    {
        $this->routeLoaders[] = $routeLoader;

        return $this;
    }
}
