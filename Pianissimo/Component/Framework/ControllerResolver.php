<?php

namespace Pianissimo\Component\Framework;

use Pianissimo\Component\DependencyInjection\ContainerInterface;
use Pianissimo\Component\HttpFoundation\Exception\NotFoundHttpException;
use Psr\Http\Message\ServerRequestInterface;

class ControllerResolver
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Router
     */
    private $router;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->router = $container->get(Router::class);
    }

    public function resolve(ServerRequestInterface $request): Callable
    {
        $router = $this->router;

        $path = $_SERVER['PATH_INFO'] ?? '';
        $route = $router->matchRoute($path);

        if ($route === null) {
            throw new NotFoundHttpException('404 Not Found');
        }

        $class = $route->getClass();
        $method = $route->getFunction();

        $controller = $this->container->get($class);

        return function () use ($controller, $method) {
            return $controller->$method();
        };
    }
}
