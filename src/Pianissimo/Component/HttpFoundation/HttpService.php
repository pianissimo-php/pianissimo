<?php

namespace App\Pianissimo\Component\HttpFoundation;

use App\Pianissimo\Component\Container\Container;
use App\Pianissimo\Component\HttpFoundation\Exception\NotFoundHttpException;
use App\Pianissimo\Component\Routing\RoutingService;
use ReflectionClass;

class HttpService
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
        if ($response instanceof RedirectResponse) {
            $header = sprintf('Location: %s', $response->getRedirectUrl());
            header($header, true, $response->getStatusCode());
            exit;
        }

        $pianoTuner = $this->container->getSetting('piano-tuner');

        http_response_code($response->getStatusCode());
        header('Content-Type: text/html');

        // Clean the output buffer
        ob_clean();

        echo $response->getContent();
        echo  $pianoTuner ? $this->pianoTuner($response) : '';
        exit;
    }

    private function pianoTuner(Response $response): string
    {
        $controllerInfo = (new ReflectionClass($response->getControllerClass()))->getShortName() . '::' . $response->getControllerFunction();
        $originInfo = $response->getRoute() ? $response->getRoute()->getName() : $controllerInfo;

        $codeColor = '#6ab04c';
        if ($response->getStatusCode() !== 200) {
            $codeColor = '#eb4d4b';
        }

        return '
            <div style="font-family: Verdana; font-size: 14px; background: black; position: fixed; 
                bottom: 0; left: 0; right: 0; height: 40px; color: white;">
                <div style="background: ' . $codeColor . '; float: left; height: 100%; padding: 10px;">' . $response->getStatusCode() . '</div>
                <div style="background: #1e272e; float: left; height: 100%; padding: 10px;">PianoTuner @' . $originInfo . ' </div>
                <div style="background: #535c68; float: left; height: 100%; padding: 10px;">' . $_SERVER['REQUEST_METHOD'] . ' </div>
            </div>
        ';
    }
}