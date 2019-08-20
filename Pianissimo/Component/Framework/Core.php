<?php

namespace Pianissimo\Component\Framework;

use Pianissimo\Component\Config\DelegatingLoader;
use Pianissimo\Component\Config\LoaderInterface;
use Pianissimo\Component\Config\LoaderResolver;
use Pianissimo\Component\DependencyInjection\ContainerBuilder;
use Pianissimo\Component\Framework\Bridge\Doctrine\Command\UpdateCommand;
use Pianissimo\Component\Framework\Loader\YamlFileLoader;
use Pianissimo\Component\Framework\PianoTuner\PianoTuner;
use Pianissimo\Component\Framework\Controller\ErrorController;
use Pianissimo\Component\Framework\Controller\ExceptionController;
use Pianissimo\Component\Framework\Routing\Command\DebugRoutesCommand;
use Pianissimo\Component\HttpFoundation\Exception\NotFoundHttpException;
use Pianissimo\Component\HttpFoundation\Request;
use Pianissimo\Component\HttpFoundation\Response;
use ReflectionObject;
use Throwable;
use UnexpectedValueException;

class Core
{
    /**
     * @var ContainerBuilder
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

    /**
     * @var string
     */
    private $projectDir;

    public function __construct(string $environment, bool $debug)
    {
        $this->startTime = microtime(true);

        $this->environment = $environment;
        $this->setDebugMode($debug);
    }

    public function boot(): void
    {
        if ($this->booted === true) {
            return;
        }

        $this->initializeContainer();
        $this->addParameters();
        $this->container->build();

        $this->booted = true;
    }

    private function initializeContainer(): void
    {
        $container = new ContainerBuilder();

        $container->setDefaultAutowiring(true);

        $containerLoader = $this->getContainerLoader($container);

        $containerLoader->load(__DIR__ . DIRECTORY_SEPARATOR . 'services.yaml');

        if (method_exists($this, 'configureContainer')) {
            $this->configureContainer($containerLoader);
        }

        $this->container = $container;
    }

    private function getContainerLoader(ContainerBuilder $container): LoaderInterface
    {
        $loaders = [
            new YamlFileLoader($container),
        ];

        $loaderResolver = new LoaderResolver($loaders);

        return new DelegatingLoader($loaderResolver);
    }

    private function addParameters(): void
    {
        $this->container->setParameter('project.dir', $this->getProjectDir());
    }

    /**
     * @throws NotFoundHttpException
     */
    public function handle(Request $request): Response
    {
        $this->boot();

        $controllerResolver = new ControllerResolver($this->container);

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
            set_error_handler([$this, 'errorHandler'], E_ALL);
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
        $this->send($response);
    }

    /**
     * Calls the ExceptionController and handles it's Response
     */
    public function exceptionHandler(Throwable $exception): void
    {
        if ($this->container === null || $this->container->isBuilt() === false) {
            dd($exception);
        }

        $exceptionController = $this->container->get(ExceptionController::class);
        $response = $exceptionController->index($exception);
        $this->send($response);
    }

    /**
     * Gets the application root dir (path of the project's composer file).
     */
    public function getProjectDir(): string
    {
        if ($this->projectDir === null) {
            $reflectionObject = new ReflectionObject($this);
            $dir = $rootDir = dirname($reflectionObject->getFileName());

            while (!file_exists($dir . '/composer.json')) {
                if ($dir === dirname($dir)) {
                    return $this->projectDir = $rootDir;
                }
                $dir = dirname($dir);
            }

            $this->projectDir = $dir;
        }

        return $this->projectDir;
    }

    public function getContainer(): ContainerBuilder
    {
        return $this->container;
    }

    public function getStartTime(): float
    {
        return $this->startTime;
    }

    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * @TODO command locator
     */
    public function getCommands(): array
    {
        return [
            DebugRoutesCommand::class,
            UpdateCommand::class,
        ];
    }
}
