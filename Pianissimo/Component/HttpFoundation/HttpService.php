<?php

namespace Pianissimo\Component\HttpFoundation;

use Pianissimo\Component\Container\Container;
use Pianissimo\Component\HttpFoundation\Exception\NotFoundHttpException;
use Pianissimo\Component\Routing\RoutingService;
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
        echo $pianoTuner === true && $response->isRendered() === true ? $this->pianoTuner($response) : '';
        exit;
    }

    private function pianoTuner(Response $response): string
    {
        $controllerInfo = (new ReflectionClass($response->getControllerClass()))->getShortName() . '::' . $response->getControllerFunction();
        $originInfo = $response->getRoute() ? $response->getRoute()->getName() : $controllerInfo;

        // Generate a hash to prevent outside manipulation/conflicts of/with the PianoTuner elements
        $hashToolbar = '_' . base_convert(md5(random_int(0,999)), 10, 36);
        $hashFunction = '_' . base_convert(md5(random_int(0,999)), 10, 36);

        $codeColor = '#6ab04c';
        if ($response->getStatusCode() !== 200) {
            $codeColor = '#eb4d4b';
        }

        return '
            <div id="' . $hashToolbar . '" style="font-family: Verdana; font-size: 14px; background: black; position: fixed; 
                bottom: 0; left: 0; right: 0; height: 40px; color: white;">
                <div style="background: ' . $codeColor . '; float: left; height: 100%; padding: 10px;">' . $response->getStatusCode() . '</div>
                <div style="background: #1e272e; float: left; height: 100%; padding: 10px;">PianoTuner @' . $originInfo . ' </div>
                <div style="background: #535c68; float: left; height: 100%; padding: 10px;">' . $_SERVER['REQUEST_METHOD'] . ' </div>
            </div>
            <div style="background: #22a6b3; font-family: Verdana; width: 40px; height: 40px; vertical-align: middle;
                font-size: 20px; position: fixed; bottom: 0; right: 0; line-height: 35px; padding: 0px; cursor: pointer;
                color: white; text-align: center;" onclick="' . $hashFunction . '()">&#119070;</div>
            <script>
                if (typeof(Storage) !== "undefined" && localStorage.pianoTuner === "false") {
                    document.getElementById("' . $hashToolbar . '").style.display = "none";
                }
                    
                function ' . $hashFunction . '() {
                  const x = document.getElementById("' . $hashToolbar . '");
                  if (x.style.display === "none") {
                    x.style.display = "block";
                    
                    if (typeof(Storage) !== "undefined") {
                      localStorage.pianoTuner = true;
                    }
                  } else {
                    x.style.display = "none";
                    
                    if (typeof(Storage) !== "undefined") {
                      localStorage.pianoTuner = false;
                    }
                  }
                }
            </script>
        ';
    }
}