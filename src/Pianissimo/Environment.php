<?php

namespace App\Pianissimo;

use App\Pianissimo\Component\HttpFoundation\Controller\ErrorController;
use App\Pianissimo\Component\HttpFoundation\Controller\ExceptionController;
use App\Pianissimo\Component\HttpFoundation\HttpService;
use Throwable;

class Environment
{
    /** @var HttpService */
    private $httpService;

    /** @var Container */
    private $container;

    public function __construct(HttpService $httpService, Container $container)
    {
        $this->httpService = $httpService;
        $this->container = $container;

        if (ENV === 'dev') {
            $this->setDebugMode(true);
        }
        if (ENV === 'prod') {
            $this->setDebugMode(false);
        }
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
     * TODO controller parameters -> request
     */
    public function errorHandler($errorNo, $errorString, $errorFile, $errorLine): void
    {
        $errorController = $this->container->get(ErrorController::class);
        $response = $errorController->index($errorNo, $errorString, $errorFile, $errorLine);

        $this->httpService->handleResponse($response);
    }

    /**
     * Calls the ExceptionController and handles it's Response
     */
    public function exceptionHandler(Throwable $exception): void
    {
        $exceptionController = $this->container->get(ExceptionController::class);
        $response = $exceptionController->index($exception);

        $this->httpService->handleResponse($response);
    }
}