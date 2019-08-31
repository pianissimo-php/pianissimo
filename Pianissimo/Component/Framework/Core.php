<?php

namespace Pianissimo\Component\Framework;

use Pianissimo\Component\Config\DelegatingLoader;
use Pianissimo\Component\Config\LoaderInterface;
use Pianissimo\Component\Config\LoaderResolver;
use Pianissimo\Component\DependencyInjection\ContainerBuilder;
use Pianissimo\Component\DependencyInjection\ContainerInterface;
use Pianissimo\Component\Framework\Bridge\Doctrine\Command\CreateCommand;
use Pianissimo\Component\Framework\Bridge\Doctrine\Command\UpdateCommand;
use Pianissimo\Component\Framework\Exception\NotFoundHttpException;
use Pianissimo\Component\Framework\Loader\YamlFileLoader;
use Pianissimo\Component\Framework\Controller\ErrorController;
use Pianissimo\Component\Framework\Controller\ExceptionController;
use Pianissimo\Component\Framework\Routing\Command\DebugRoutesCommand;
use ReflectionObject;
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

        $containerBuilder = $this->initializeContainer();
        $this->container = $containerBuilder;

        $cachedContainerFile = $containerBuilder->getCachedContainerFile();

        if (file_exists($cachedContainerFile) === true) {
            $this->container = $containerBuilder->getCachedContainer();
            $this->booted = true;

            return;
        }

        $containerBuilder->build();
        $this->container = $containerBuilder->dump();

        $this->booted = true;
    }

    private function initializeContainer(): ContainerBuilder
    {
        $container = new ContainerBuilder();

        $container->setDefaultAutowiring(true);

        $containerLoader = $this->getContainerLoader($container);

        $containerLoader->load(__DIR__ . DIRECTORY_SEPARATOR . 'services.yaml');

        if (method_exists($this, 'configureContainer')) {
            $this->configureContainer($containerLoader);
        }

        $container->setParameter('environment', $this->getEnvironment());
        $container->setParameter('project_dir', $this->getProjectDir());

        return $container;
    }

    private function getContainerLoader(ContainerBuilder $container): LoaderInterface
    {
        $loaders = [
            new YamlFileLoader($container),
        ];

        $loaderResolver = new LoaderResolver($loaders);

        return new DelegatingLoader($loaderResolver);
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
     */
    public function send(Response $response): void
    {
        //PianoTuner::get($response, $this->startTime);

        //$executionTime = round((microtime(true) - $this->startTime) * 1000);
        //dd($executionTime);

        $response->send();
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
        if ($this->container instanceof ContainerBuilder && $this->container->isBuilt() === false) {
            dd($errorString);
        }

        $errorController = $this->container->get(ErrorController::class);
        $response = $errorController->index($errorNo, $errorString, $errorFile, $errorLine);
        $this->send($response);
    }

    /**
     * Calls the ExceptionController and handles it's Response
     */
    public function exceptionHandler(Throwable $exception): void
    {
        if ($this->container === null || ($this->container instanceof ContainerBuilder && $this->container->isBuilt() === false)) {
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

    private function getCacheDir(): string
    {
        // Temporary solution
        $projectDir = $this->container->getParameter('project_dir');
        $cacheDir = $this->container->getParameter('cache_dir');

        return $projectDir . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . $cacheDir;
    }

    public function getContainer(): ContainerInterface
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
            CreateCommand::class,
            UpdateCommand::class,
        ];
    }
}
