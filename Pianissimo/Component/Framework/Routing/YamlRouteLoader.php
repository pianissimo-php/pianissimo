<?php

namespace Pianissimo\Component\Framework\Routing;

use Pianissimo\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Pianissimo\Component\Routing\Route;
use Pianissimo\Component\Routing\RouteCollection;
use Pianissimo\Component\Routing\RouteLoaderInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class YamlRouteLoader implements RouteLoaderInterface
{
    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    public function load(): RouteCollection
    {
        $routeCollection = new RouteCollection();

        $file = $this->parameterBag->get('project_dir') . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'routes.yaml';
        $data = Yaml::parseFile($file);

        if (array_key_exists('routes', $data) === false) {
            return $routeCollection;
        }

        foreach ($data['routes'] as $name => $route) {
            if (array_key_exists('path', $route) === false) {
                throw new ParseException(sprintf("Route '%s' must contain '%s'", $name, 'path'));
            }

            if (array_key_exists('class', $route) === false) {
                throw new ParseException(sprintf("Route '%s' must contain '%s'", $name, 'class'));
            }

            if (class_exists($route['class']) === false) {
                throw new ParseException(sprintf("Class '%s' in route '%s' does not exists", $route['class'], $name));
            }

            if (array_key_exists('method', $route) === false) {
                throw new ParseException(sprintf("Route '%s' must contain '%s'", $name, 'method'));
            }

            if (method_exists($route['class'], $route['method']) === false) {
                throw new ParseException(sprintf("Method '%s' in class '%s' in route definition '%s' does not exists", $route['method'], $route['class'], $name));
            }

            $route = new Route(
                $route['class'],
                $route['method'],
                $route['path'],
                $name
            );

            $routeCollection->add($route);
        }

        return $routeCollection;
    }
}
