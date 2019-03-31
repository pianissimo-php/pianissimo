<?php

namespace App\Pianissimo\Component\HttpFoundation;

use App\Pianissimo\Component\HttpFoundation\Exception\NotFoundHttpException;
use App\Pianissimo\Component\Routing\RoutingService;

class HttpService
{
    /** @var RoutingService */
    private $routingService;

    public function __construct(RoutingService $routingService)
    {
        $this->routingService = $routingService;
    }

    /**
     * Each request wil go through this function. From this place, all services will be auto wired!
     *
     * @throws NotFoundHttpException
     */
    public function getResponse(Request $request): Response
    {
        $this->routingService->initializeRoutes();

        $path = $_SERVER['PATH_INFO'] ?? '';
        $route = $this->routingService->matchRoute($path);

        if ($route === null) {
            throw new NotFoundHttpException('404 Not Found');
        }

        return $this->routingService->handleRoute($route);
    }

    /**
     * 'Executes' the given Response
     */
    public function handleResponse(Response $response): void
    {
        http_response_code($response->getStatusCode());
        header('Content-Type: text/html');

        // Clean the output buffer
        ob_clean();

        echo $response->getContent();
        exit;
    }
}