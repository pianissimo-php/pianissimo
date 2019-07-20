<?php

namespace Pianissimo\Component\Routing;

use App\Controller\IndexController;
use Pianissimo\Component\Annotation\AnnotationReader;
use Pianissimo\Component\Routing\Exception\RouteNotFoundException;

class RoutingService
{
    /**
     * @var RouteRegistry
     */
    private $routeRegistry;

    /**
     * @var AnnotationReader
     */
    private $annotationReader;

    public function __construct(RouteRegistry $routeRegistry, AnnotationReader $annotationReader)
    {
        $this->routeRegistry = $routeRegistry;
        $this->annotationReader = $annotationReader;
    }

    /**
     * Returns the registry with the initialized routes.
     */
    public function getRegistry(): array
    {
        return $this->routeRegistry->all();
    }

    /**
     * Handles all route loaders and registers their routes in the registry.
     */
    public function initializeRoutes(): void
    {
        $routes = $this->getAnnotationRoutes();
        $this->routeRegistry->initialize($routes);
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
     * Returns all routes defined by annotations in the controllers.
     */
    public function getAnnotationRoutes(): array
    {
        $routes = [];
        $classes = $this->findControllerClasses();

        foreach ($classes as $class) {
            $functions = get_class_methods($class);

            if ($functions === null) {
                continue;
            }

            foreach ($functions as $function) {
                $annotations = $this->annotationReader->getFunctionAnnotations($class, $function, 'Route');

                foreach ($annotations as $annotation) {
                    $route = new Route($class, $function, $annotation->path, $annotation->name);
                    $routes[] = $route;
                }
            }
        }

        return $routes;
    }

    /**
     * Returns all Controller classes
     * TODO improve logic
     */
    private function findControllerClasses(): array
    {
        return [
            IndexController::class,
        ];
        //return $this->container->getSetting('controllers');
    }
}
