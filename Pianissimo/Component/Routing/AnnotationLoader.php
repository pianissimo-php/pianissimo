<?php
/**
 * Created by PhpStorm.
 * User: jonmulder
 * Date: 2019-04-09
 * Time: 19:25
 */

namespace Pianissimo\Component\Routing;

use Pianissimo\Component\Annotation\AnnotationReader;
use Pianissimo\Component\Container\Container;

class AnnotationLoader implements RouteLoaderInterface
{
    /** @var Container */
    private $container;

    /** @var AnnotationReader */
    private $annotationReader;

    public function __construct(Container $container, AnnotationReader $annotationReader)
    {
        $this->container = $container;
        $this->annotationReader = $annotationReader;
    }

    /**
     * Returns all routes defined by annotations in the controllers.
     */
    public function load(): array
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
        return $this->container->getSetting('controllers');
    }
}