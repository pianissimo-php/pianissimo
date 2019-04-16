<?php

namespace Pianissimo\Component\HttpFoundation;

use Pianissimo\Component\Container\Container;
use Pianissimo\Component\HttpFoundation\Exception\NotFoundHttpException;
use Pianissimo\Component\Routing\Route;
use Pianissimo\Component\Routing\RoutingService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use UnexpectedValueException;

class RequestHandler implements RequestHandlerInterface
{
    /** @var RoutingService */
    private $routingService;

    /** @var Container */
    private $container;

    public function __construct(RoutingService $routingService, Container $container)
    {
        $this->routingService = $routingService;
        $this->container = $container;
    }

    /**
     * Handles a request and produces a response.
     * May call other collaborating code to generate the response.
     *
     * Each request wil go through this function. From this place, all services will be auto wired!
     * @throws NotFoundHttpException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->routingService->initializeRoutes();

        $path = $_SERVER['PATH_INFO'] ?? '';
        $route = $this->routingService->matchRoute($path);

        if ($route === null) {
            throw new NotFoundHttpException('404 Not Found');
        }

        return $this->handleRoute($route);
    }

    /**
     * Calls the matching controller function and returns its Response object.
     */
    public function handleRoute(Route $route): Response
    {
        $class = $route->getClass();
        $function = $route->getFunction();

        $controller = $this->container->autowire($class);

        /** @var Response $response */
        $response = $controller->$function();
        $response->setRoute($route);

        if (!$response instanceof Response) {
            throw new UnexpectedValueException(sprintf("Function '%s' in controller class '%s' must return an instance of '%s', '%s' given.", $function, $class, Response::class, gettype($response)));
        }

        return $response;
    }
}