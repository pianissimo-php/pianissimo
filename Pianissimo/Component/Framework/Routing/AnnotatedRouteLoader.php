<?php

namespace Pianissimo\Component\Framework\Routing;

use Pianissimo\Component\Annotation\AnnotationReader;
use Pianissimo\Component\Routing\Route;
use Pianissimo\Component\Routing\RouteCollection;
use Pianissimo\Component\Routing\RouteLoaderInterface;

class AnnotatedRouteLoader implements RouteLoaderInterface
{
    /**
     * @var AnnotationReader
     */
    private $annotationReader;

    /**
     * @var string[]|array
     */
    private $controllerClasses;

    public function __construct(AnnotationReader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
        $this->controllerClasses = [];
    }

    /**
     * Returns all routes defined by annotations in the controllers.
     */
    public function load(): RouteCollection
    {
        $routes = new RouteCollection();
        $classes = $this->controllerClasses;

        foreach ($classes as $class) {
            $functions = get_class_methods($class);

            if ($functions === null) {
                continue;
            }

            foreach ($functions as $function) {
                $annotations = $this->annotationReader->getFunctionAnnotations($class, $function, 'Route');

                foreach ($annotations as $annotation) {
                    $route = new Route($class, $function, $annotation->path, $annotation->name);
                    $routes->add($route);
                }
            }
        }

        return $routes;
    }

    /**
     * Method is called by RouterControllerCompilerPass.
     */
    public function addController(string $controllerClass): void
    {
        $this->controllerClasses[] = $controllerClass;
    }
}
