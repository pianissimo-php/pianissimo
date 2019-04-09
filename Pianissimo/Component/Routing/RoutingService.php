<?php

namespace Pianissimo\Component\Routing;

use Pianissimo\Component\Annotation\AnnotationReader;
use Pianissimo\Component\Container\Container;
use Pianissimo\Component\HttpFoundation\Exception\NotFoundHttpException;
use Pianissimo\Component\HttpFoundation\Response;
use UnexpectedValueException;

class RoutingService
{
    /** @var Container */
    private $container;

    /** @var AnnotationReader */
    private $annotationReader;

    /** @var RouteRegistry */
    private $routeRegistry;

    public function __construct(Container $container, AnnotationReader $annotationReader, RouteRegistry $routeRegistry)
    {
        $this->container = $container;
        $this->annotationReader = $annotationReader;
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
     * Initializes all routes and it registers them in the registry.
     */
    public function initializeRoutes(): void
    {
        $routes = $this->findControllerRoutes();
        $this->routeRegistry->initialize($routes);
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

    /**
     * Returns all routes in the controllers.
     */
    private function findControllerRoutes(): array
    {
        $routes = [];
        $classes = $this->findControllerClasses();

        foreach ($classes as $class) {
            $functions = get_class_methods($class);

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
        return $this->container->getSetting('controllers');
    }
}