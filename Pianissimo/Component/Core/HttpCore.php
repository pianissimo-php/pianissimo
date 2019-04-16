<?php

namespace Pianissimo\Component\Core;

use Pianissimo\Component\HttpFoundation\Controller\ErrorController;
use Pianissimo\Component\HttpFoundation\Controller\ExceptionController;
use Pianissimo\Component\HttpFoundation\RedirectResponse;
use Pianissimo\Component\HttpFoundation\Request;
use Pianissimo\Component\HttpFoundation\RequestHandler;
use Pianissimo\Component\HttpFoundation\Response;
use ReflectionClass;
use Throwable;

class HttpCore extends Core
{
    /** @var RequestHandler */
    private $requestHandler;

    /** @var float */
    private $startTime;

    /** @var string */
    private $environment;

    /**
     * HttpCore constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->requestHandler = $this->container->autowire(RequestHandler::class);

        // Start timer
        $this->startTime = microtime(true);

        // TODO Error & Exception handling
        $environment = $this->container->getSetting('environment');
        $this->environment = $environment;

        if ($environment === 'dev') {
            $this->setDebugMode(true);
        }
        if ($environment === 'prod') {
            $this->setDebugMode(false);
        }
    }

    public function handleRequest(Request $request): Response
    {
        return $this->requestHandler->handle($request);
    }

    /**
     * 'Executes' the given Response
     */
    /**
     * Send a HTTP response
     *
     * @return void
     */
    public function handleResponse(Response $response)
    {
        $http_line = sprintf('HTTP/%s %s %s',
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            $response->getReasonPhrase()
        );
        header($http_line, true, $response->getStatusCode());
        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header("$name: $value", false);
            }
        }
        $stream = $response->getBody();

        $pianoTuner = $this->container->getSetting('piano_tuner');
        echo $pianoTuner === true && $response->isRendered() === true ? $this->pianoTuner($response) : '';

        if ($stream->isSeekable()) {
            $stream->rewind();
        }
        while (!$stream->eof()) {
            echo $stream->read(1024 * 8);
        }
        die;
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
        $exceptionController = $this->container->autowire(ExceptionController::class);
        $response = $exceptionController->index($exception);

        $this->handleResponse($response);
    }
}