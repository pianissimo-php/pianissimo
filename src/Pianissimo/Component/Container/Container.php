<?php

namespace App\Pianissimo\Component\Container;

use App\Pianissimo\Component\Container\Exception\ConfigurationFileException;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;

/**
 * The Container holds the registry with initialized Services and the initialized Configuration.
 */
class Container implements RegistryInterface
{
    /** @var array */
    private $registry;

    /** @var RouteRegistry */
    public $routeRegistry;

    /** @var ConfigurationRegistry */
    private $configurationRegistry;

    /**
     * Initialize the Container
     * @throws ConfigurationFileException
     */
    public function __construct()
    {
        // Initialize registries.
        $this->registry = [];
        $this->routeRegistry = new RouteRegistry();
        $this->configurationRegistry = new ConfigurationRegistry();

        // Inject this instance of the container in the registry
        $this->registry[__CLASS__] = $this;

        // Load ConfigurationHandler manually, handler hasn't to be available in de service container
        $configurationService = new ConfigurationHandler();
        $this->configurationRegistry->initialize($configurationService->load());
    }

    /**
     * This functions gets an object out of the registry.
     * If it is not in the registry, it adds an new instance of the class to the registry.
     */
    public function get(string $className)
    {
        if (isset($this->registry[$className]) === false) {
            $this->set($className);
        }

        return $this->registry[$className];
    }

    /**
     * This function creates an new instance of the given class name and adds it to the registry.
     * It will auto wire the parameters of the new instance. (Dependency Injection)
     */
    private function set(string $className): void
    {
        if (class_exists($className) === false) {
            throw new InvalidArgumentException(sprintf("Not able to auto wire class '%s', class does not exist.", $className));
        }

        // The heart of the Dependency Injection.
        $class = new ReflectionClass($className);
        $constructor = $class->getConstructor();
        $parentClass = $class->getParentClass();

        $parameters = [];

        if ($constructor !== null) {
            $autoWiredParameters = $this->autoWireMethod($constructor);
            $parameters = array_merge($parameters, $autoWiredParameters);
        }

        $instance = new $className(...$parameters);

        $this->registry[$className] = $instance;
    }

    /**
     * Checks if Service exists in the registry
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->registry);
    }

    /**
     * Auto wires the parameters of the given ReflectionMethod.
     */
    private function autoWireMethod(ReflectionMethod $method): array
    {
        $parameters = $method->getParameters();

        $autoWiredParameters = [];
        foreach ($parameters as $parameter) {
            $parameterClass = $parameter->getClass()->getName();
            $autoWiredParameters[] = $this->get($parameterClass);
        }

        return $autoWiredParameters;
    }

    /**
     * Returns an Configuration item
     */
    public function getSetting(string $setting)
    {
        return $this->configurationRegistry->get($setting);
    }
}