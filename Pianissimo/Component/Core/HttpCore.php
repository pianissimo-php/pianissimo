<?php

namespace Pianissimo\Component\Core;

use Pianissimo\Component\HttpFoundation\Controller\ErrorController;
use Pianissimo\Component\HttpFoundation\Controller\ExceptionController;
use Pianissimo\Component\HttpFoundation\Request;
use Pianissimo\Component\HttpFoundation\RequestHandler;
use Pianissimo\Component\HttpFoundation\Response;
use ReflectionClass;
use Throwable;

class HttpCore extends Core
{


    /**
     * HttpCore constructor.
     */


    public function getStartTime(): float
    {
        return $this->startTime;
    }

    public function getEnvironment(): string
    {
        return $this->environment;
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
