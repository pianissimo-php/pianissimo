<?php

namespace Pianissimo\Component\Framework\Routing;

use Doctrine\Common\Annotations\AnnotationReader;
use Pianissimo\Component\Routing\Annotation\Route as RouteAnnotation;
use Pianissimo\Component\Routing\Route;
use Pianissimo\Component\Routing\RouteCollection;
use Pianissimo\Component\Routing\RouteLoaderInterface;
use ReflectionMethod;

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
                $reflectionMethod = new ReflectionMethod($class, $function);
                $annotations = $this->annotationReader->getMethodAnnotations($reflectionMethod);

                if ($annotations === null) {
                    continue;
                }

                foreach ($annotations as $annotation) {
                    if (!$annotation instanceof RouteAnnotation) {
                        continue;
                    }

                    $route = new Route(
                        $class,
                        $function,
                        $annotation->path,
                        $annotation->name
                    );

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
