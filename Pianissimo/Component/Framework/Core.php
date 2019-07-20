<?php

namespace Pianissimo\Component\Framework;

use App\Controller\IndexController;
use Pianissimo\Component\Annotation\AnnotationReader;
use Pianissimo\Component\DependencyInjection\ContainerBuilder;
use Pianissimo\Component\DependencyInjection\ContainerInterface;
use Pianissimo\Component\Framework\Command\DebugRoutesCommand;
use Pianissimo\Component\Framework\PianoTuner\PianoTuner;
use Pianissimo\Component\HttpFoundation\Controller\ErrorController;
use Pianissimo\Component\HttpFoundation\Controller\ExceptionController;
use Pianissimo\Component\HttpFoundation\Exception\NotFoundHttpException;
use Pianissimo\Component\HttpFoundation\Request;
use Pianissimo\Component\HttpFoundation\Response;
use Pianissimo\Component\Routing\RouteRegistry;
use Pianissimo\Component\Routing\RoutingService;
use Throwable;
use UnexpectedValueException;

class Core
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var float
     */
    private $startTime;

    /**
     * @var string
     */
    private $environment;

    /**
     * @var bool
     */
    private $booted;

    public function __construct(string $environment, bool $debug)
    {
        $this->startTime = microtime(true);

        $this->environment = $environment;
        $this->setDebugMode($debug);
}

    private function boot(): void
    {
        if ($this->booted === true) {
            return;
        }

        $this->initializeContainer();
        $this->booted = true;

        $this->container
            ->register('controller.index', IndexController::class)
            ->setAutowired(true);

        $this->container->build();
    }

    private function initializeContainer(): void
    {
        $this->container = new ContainerBuilder();

        /*
        $this->container
            ->register('annotation.reader', AnnotationReader::class)
            ->setAutowired(true);

        $this->container
            ->register('jon', ControllerService::class)
            ->setAutowired(true);

        $this->container
            ->register('bob', RoutingService::class)
            ->setAutowired(true);

        $this->container
            ->register('kees', RouteRegistry::class);
        */
    }

    /**
     * @throws NotFoundHttpException
     */
    public function handle(Request $request): Response
    {
        $this->boot();

        $routingService = new RoutingService(new RouteRegistry(), new AnnotationReader());
        $controllerResolver = new ControllerResolver($routingService, $this->container);

        $controllerCallable = $controllerResolver->resolve($request);

        $response = $controllerCallable();
        //$response->setRoute($route);

        if (!$response instanceof Response) {
            //throw new UnexpectedValueException(sprintf("Function '%s' in controller class '%s' must return an instance of '%s', '%s' given.", $function, $class, Response::class, gettype($response)));
            throw new UnexpectedValueException(sprintf("Function must return an instance of '%s'.", Response::class));
        }

        return $response;
    }

    /**
     * Sends the given Response
     * Send a HTTP response
     *
     * @return void
     */
    public function send(Response $response): void
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

        // TODO fix debugging toolbar
        //$pianoTuner = $this->container->getSetting('piano_tuner');
        //echo $pianoTuner === true && $response->isRendered() === true ? $this->pianoTuner($response) : '';
        echo $response->isRendered() === true ? PianoTuner::pianoTuner($response, $this->startTime) : '';

        if ($stream->isSeekable()) {
            $stream->rewind();
        }
        while (!$stream->eof()) {
            echo $stream->read(1024 * 8);
        }
        die;
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
        dd($errorString); //todo
    }

    /**
     * Calls the ExceptionController and handles it's Response
     */
    public function exceptionHandler(Throwable $exception): void
    {
        dd($exception); //todo
    }

    public function getStartTime(): float
    {
        return $this->startTime;
    }

    public function getEnvironment(): string
    {
        return $this->environment;
    }

    public function getCommands(): array
    {
        return [
            DebugRoutesCommand::class,
        ];
    }
}
