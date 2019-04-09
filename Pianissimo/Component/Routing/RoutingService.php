<?php

namespace Pianissimo\Component\Routing;

use Pianissimo\Component\Container\Container;
use Pianissimo\Component\Container\Exception\ClassNotFoundException;
use Pianissimo\Component\Finder\Path;
use Pianissimo\Component\HttpFoundation\Exception\NotFoundHttpException;
use Pianissimo\Component\HttpFoundation\Response;
use Pianissimo\Component\Routing\Exception\RouteLoaderException;
use Pianissimo\Component\Routing\Exception\RouteLoaderNotFoundException;
use Symfony\Component\Yaml\Yaml;
use UnexpectedValueException;

class RoutingService
{
    /** @var Container */
    private $container;

    /** @var RouteRegistry */
    private $routeRegistry;

    public function __construct(Container $container, RouteRegistry $routeRegistry)
    {
        $this->container = $container;
        $this->routeRegistry = $routeRegistry;
    }

    /**
     * Returns the registry with the initialized routes.
     */
    private function getRegistry(): array
    {
        return $this->routeRegistry->all();
    }

    /**
     * Handles all route loaders and registers their routes in the registry.
     */
    public function initializeRoutes(): void
    {
        $routes = $this->handleRouteLoaders();
        $this->routeRegistry->initialize($routes);
    }

    public function handleRouteLoaders(): array
    {
        $file = Path::Root()->dir('config')->file('config.yaml')->path();
        $loaders = Yaml::parseFile($file)['route_loaders'];

        $results = [];

        foreach ($loaders as $name => $class) {
            try {
                $loader = $this->container->get($class);
            } catch (ClassNotFoundException $exception) {
                throw new RouteLoaderNotFoundException(sprintf("Route loader '%s' not found", $class));
            }

            if (!$loader instanceof RouteLoaderInterface) {
                throw new RouteLoaderException(sprintf("Route loaders must implement '%s'. See route loaders config file.", RouteLoaderInterface::class));
            }

            $results[] = $loader->load();
        }

        return array_merge(...$results);
    }

    /**
     * Calls the matching controller function and returns its Response object.
     */
    public function handleRoute(Route $route): Response
    {
        $class = $route->getClass();
        $function = $route->getFunction();

        $controller = $this->container->get($class);

        /** @var Response $response */
        $response = $controller->$function();
        $response->setRoute($route);

        if (!$response instanceof Response) {
            throw new UnexpectedValueException(sprintf("Function '%s' in controller class '%s' must return an instance of '%s', '%s' given.", $function, $class, Response::class, gettype($response)));
        }

        return $response;
    }

    /**
     * Returns the Route instance whose paths match or returns null if there are no matches.
     */
    public function matchRoute(string $path): ?Route
    {
        $routes = $this->getRegistry();

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
        $routes = $this->getRegistry();

        /** @var Route $route */
        foreach ($routes as $route) {
            if ($route->getName() === $routeName) {
                return $route;
            }
        }

        return null;
    }

    /**
     * Returns the Route instance whose names match or throws an exception if there are no matches.
     * @throws NotFoundHttpException
     */
    public function getRoute(string $routeName): Route
    {
        $route = $this->findRoute($routeName);

        if ($route === null) {
            throw new NotFoundHttpException(sprintf("Route with name '%s' not found", $routeName));
        }

        return $route;
    }
}