<?php

namespace Pianissimo\Component\Framework;

use LogicException;
use Pianissimo\Component\DependencyInjection\ContainerInterface;
use Pianissimo\Component\HttpFoundation\Exception\NotFoundHttpException;
use Pianissimo\Component\Routing\RoutingService;
use Psr\Http\Message\ServerRequestInterface;

class ControllerResolver
{
    /**
     * @var RoutingService
     */
    private $routingService;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(RoutingService $routingService, ContainerInterface $container)
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
            throw new LogicException('Route not defined as an service');
        }

        $controller = $this->container->get($class);

        return function () use ($controller, $method) {
            return $controller->$method();
        };
    }
}
