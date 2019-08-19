<?php

namespace Pianissimo\Component\Routing;

use InvalidArgumentException;
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
    public function matchRoute(string $requestPath): ?Route
    {
        $routes = $this->getRoutes();

        /** @var Route $route */
        foreach ($routes as $route) {
            if ($this->equalPaths($route->getPath(), $requestPath)) {
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

    /**
     * @throws RouteNotFoundException
     * @throws InvalidArgumentException
     */
    public function generateUrl(string $routeName, array $parameters): string
    {
        $route = $this->getRoute($routeName);

        $routePathParts = array_values(array_filter(explode('/', $route->getPath())));

        $resultParts = [];
        $usedParameters = [];

        foreach ($routePathParts as $routePathPart) {
            if ($this->isParameter($routePathPart) === false) {
                $resultParts[] = $routePathPart;
                continue;
            }

            $parameterName = str_replace(['{', '}'], [], $routePathPart);

            if (array_key_exists($parameterName, $parameters) === false) {
                throw new InvalidArgumentException(sprintf("Unable to generate the URL: required parameter '%s' is missing", $parameterName));
            }

            $resultParts[] = $parameters[$parameterName];
            $usedParameters[] = $parameterName;
        }

        $unusedParameters = array_values(array_diff(array_keys($parameters), $usedParameters));

        if (count($unusedParameters) > 0) {
            throw new InvalidArgumentException(sprintf("Unable to generate the URL: given parameter '%s' does not exists on route '%s'",
                 $unusedParameters[0], $routeName));
        }

        return '/' . implode('/', $resultParts);
    }

    /**
     * Determines whether the route path matches with the requested path.
     */
    private function equalPaths(string $routePath, string $requestPath): bool
    {
        $routePathParts = array_values(array_filter(explode('/', $routePath)));
        $requestPathParts = array_values(array_filter(explode('/', $requestPath)));

        if (count($requestPathParts) !== count($routePathParts)) {
            return false;
        }

        $match = true;
        $count = -1;

        foreach ($routePathParts as $routePathPart) {
            $count++;

            if ($this->isParameter($routePathPart)) {
                continue;
            }

            if ($routePathPart !== $requestPathParts[$count]) {
                return false;
            }
        }

        return $match;
    }

    /**
     * Returns true if the given path part is a parameter.
     */
    private function isParameter(string $part): bool
    {
        $firstCharacter = $part[0];
        $lastCharacter = substr($part, -1);

        return $firstCharacter === '{' && $lastCharacter === '}';
    }

    public function addLoader(RouteLoaderInterface $routeLoader): self
    {
        $this->routeLoaders[] = $routeLoader;

        return $this;
    }
}
