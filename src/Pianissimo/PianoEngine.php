<?php

namespace App\Pianissimo;

use App\Pianissimo\Component\HttpFoundation\Exception\NotFoundHttpException;
use App\Pianissimo\Component\HttpFoundation\Response;
use App\Pianissimo\Component\Routing\RoutingService;

class PianoEngine
{
    /** @var RoutingService */
    private $routingService;

    public function __construct(RoutingService $routingService)
    {
        $this->routingService = $routingService;
    }

    /**
     * Each request wil go through the PianoEngine. From this place, all services will be auto wired!
     *
     * @throws NotFoundHttpException
     */
    public function start(): Response
    {
        $this->routingService->initializeRoutes();

        $path = $_SERVER['PATH_INFO'] ?? '';
        $route = $this->routingService->matchRoute($path);

        if ($route === null) {
            throw new NotFoundHttpException('404 Not Found');
        }

        /*
        if (isset($_SERVER['QUERY_STRING'])) {
            dump($_SERVER['QUERY_STRING']);
        }
        */

        $response = $this->routingService->handleRoute($route);
        return $response;
    }
}