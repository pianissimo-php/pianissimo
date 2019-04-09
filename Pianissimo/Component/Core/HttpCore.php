<?php

namespace Pianissimo\Component\Core;

use Pianissimo\Component\Container\Container;
use Pianissimo\Component\HttpFoundation\Controller\ErrorController;
use Pianissimo\Component\HttpFoundation\Controller\ExceptionController;
use Pianissimo\Component\HttpFoundation\Exception\NotFoundHttpException;
use Pianissimo\Component\HttpFoundation\RedirectResponse;
use Pianissimo\Component\HttpFoundation\Request;
use Pianissimo\Component\HttpFoundation\Response;
use Pianissimo\Component\Routing\RoutingService;
use ReflectionClass;
use Throwable;

/**
 * This class is the Core class for the Http environment, with dependencies of the HttpFoundation Component.
 *
 * Configure the services that has to be used at the auto wiring for the given interfaces in config/services.yaml
 */
class HttpCore implements HttpCoreInterface
{
    /** @var Container */
    private $container;

    /** @var RoutingService */
    private $routingService;

    /** @var float */
    private $startTime;

    public function __construct()
    {
        // Initialize the container
        $this->container = new Container();

        $this->routingService = $this->container->get(RoutingService::class);

        // Start timer
        $this->startTime = microtime(true);

        // TODO Error & Exception handling
        $environment = $this->container->getSetting('environment');

        if ($environment === 'dev') {
            $this->setDebugMode(true);
        }
        if ($environment === 'prod') {
            $this->setDebugMode(false);
        }
    }

    /**
     * Each request wil go through this function. From this place, all services will be auto wired!
     *
     * @throws NotFoundHttpException
     */
    public function handleRequest(Request $request): Response
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

        $pianoTuner = $this->container->getSetting('piano_tuner');

        http_response_code($response->getStatusCode());
        header('Content-Type: text/html');

        // Clean the output buffer
        ob_clean();

        echo $response->getContent();
        echo $pianoTuner === true && $response->isRendered() === true ? $this->pianoTuner($response) : '';
        exit;
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    public function getEnvironment(): Environment
    {
        return $this->container->get(Environment::class);
    }

    public function getStartTime(): float
    {
        return $this->startTime;
    }

    /**
     * All code below this line is temporary
     */
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

        $executionTime = round(((microtime(true) - $this->getStartTime()) * 1000), 0);

        return '
            <div id="' . $hashToolbar . '" style="font-family: Verdana; font-size: 14px; background: black; position: fixed; 
                bottom: 0; left: 0; right: 0; height: 40px; color: white; padding-right: 40px;">
                <div style="background: ' . $codeColor . '; float: left; height: 100%; padding: 10px;">' . $response->getStatusCode() . '</div>
                <div style="background: #1e272e; float: left; height: 100%; padding: 10px;">PianoTuner @' . $originInfo . '</div>
                <div style="background: #22a6b3; float: left; height: 100%; padding: 10px;">' . $executionTime . ' ms</div>
                <div style="background: #535c68; float: right; height: 100%; padding: 10px;">' . $_SERVER['REQUEST_METHOD'] . '</div>
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

    private function setDebugMode(bool $mode): void
    {
        if ($mode === true) {
            set_error_handler([$this, 'errorHandler'], E_STRICT);
            set_exception_handler([$this, 'exceptionHandler']);
        } else {
            ini_set('display_errors', 0);
            ini_set('log_errors', 1);
        }
    }

    /**
     * Calls the ErrorController and handles it's Response
     */
    public function errorHandler($errorNo, $errorString, $errorFile, $errorLine): void
    {
        $errorController = $this->container->get(ErrorController::class);
        $response = $errorController->index($errorNo, $errorString, $errorFile, $errorLine);

        $this->handleResponse($response);
    }

    /**
     * Calls the ExceptionController and handles it's Response
     */
    public function exceptionHandler(Throwable $exception): void
    {
        $exceptionController = $this->container->get(ExceptionController::class);
        $response = $exceptionController->index($exception);

        $this->handleResponse($response);
    }
}