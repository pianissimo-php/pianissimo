<?php

namespace Pianissimo\Component\Framework;

use LogicException;
use Pianissimo\Component\DependencyInjection\ContainerInterface;
use Pianissimo\Component\HttpFoundation\Exception\NotFoundHttpException;
use Pianissimo\Component\Routing\Router;
use Psr\Http\Message\ServerRequestInterface;

class ControllerResolver
{
    /**
     * @var Router
     */
    private $routingService;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(Router $routingService, ContainerInterface $container)
    {
        $this->routingService = $routingService;
        $this->container = $container;
    }

    public function resolve(ServerRequestInterface $request): Callable
    {
        $this->routingService->initializeRoutes();

        $path = $_SERVER['PATH_INFO'] ?? '';
        $route = $this->routingService->matchRoute($path);

        if ($route === null) {
            throw new NotFoundHttpException('404 Not Found');
        }

        $class = $route->getClass();
        $method = $route->getFunction();

        if ($this->container->has($class) === false) {
            throw new LogicException('Controller not defined as an service');
        }

        $controller = $this->container->get($class);

        return function () use ($controller, $method) {
            return $controller->$method();
        };
    }
}
