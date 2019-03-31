<?php

namespace App\Pianissimo\Component\Routing;

use App\Pianissimo\Component\Annotation\AnnotationReader;
use App\Controller\IndexController;
use App\Pianissimo\Component\Container\Container;
use App\Pianissimo\Component\HttpFoundation\Response;
use UnexpectedValueException;

class RoutingService
{
    /** @var array */
    private $registry;

    /** @var AnnotationReader */
    private $annotationReader;

    /** @var Container */
    private $container;

    public function __construct(AnnotationReader $annotationReader, Container $container)
    {
        $this->registry = [];
        $this->annotationReader = $annotationReader;
        $this->container = $container;
    }

    /**
     * Returns the registry with the initialized routes.
     */
    private function getRegistry(): array
    {
        return $this->registry;
    }

    /**
     * Adds a new record to the route registry.
     */
    public function register(Route $route): void
    {
        $this->registry[] = $route;
    }

    /**
     * Initializes all routes and it registers them in the registry.
     */
    public function initializeRoutes(): void
    {
        $this->registerControllerRoutes();
    }

    /**
     * Calls the matching controller function and returns its Response object.
     */
    public function handleRoute(Route $route): Response
    {
        $class = $route->getClass();
        $function = $route->getFunction();

        $controller = $this->container->get($class);
        $response = $controller->$function();

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
     * Registers all routes (in all controllers) in the registry.
     */
    private function registerControllerRoutes(): void
    {
        $classes = $this->findControllerClasses();

        foreach ($classes as $class) {
            $functions = get_class_methods($class);

            foreach ($functions as $function) {
                $annotations = $this->annotationReader->getFunctionAnnotations($class, $function, 'Route');

                foreach ($annotations as $annotation) {
                    $route = new Route($class, $function, $annotation->path, $annotation->name);
                    $this->register($route);
                }
            }
        }
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