<?php

namespace App\Pianissimo\Component\Routing;

use App\Pianissimo\Component\Annotation\AnnotationReader;
use App\Controller\IndexController;

class RoutingService
{
    /** @var array */
    private $registry;

    /** @var AnnotationReader */
    private $annotationReader;

    public function __construct(AnnotationReader $annotationReader)
    {
        $this->registry = [];
        $this->annotationReader = $annotationReader;
    }

    private function getRegistry(): array
    {
        return $this->registry;
    }

    public function register(Route $route): void
    {
        $this->registry[] = $route;
    }

    public function initializeRoutes(): void
    {
        $this->registerControllerRoutes();
    }

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

    private function findControllerClasses(): array
    {
        //$match = preg_match('/Controller\\\\(.*)Controller/', $class);
        //return $match === 1;

        return [
            IndexController::class,
        ];
    }
}