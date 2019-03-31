<?php

namespace App\Pianissimo\Component\Container;

use App\Pianissimo\Component\Container\Exception\ConfigurationFileException;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * The Container holds the registry with initialized Services and the initialized Configuration.
 */
class Container
{
    /** @var array */
    private $registry;

    /** @var array */
    private $configuration;

    /**
     * Initialize the Container
     * @throws ConfigurationFileException
     */
    public function __construct()
    {
        // Load ConfigurationHandler manually, handler hasn't to be available in de service container
        $configurationService = new ConfigurationHandler();
        $this->configuration = $configurationService->load();
    }

    /**
     * This functions gets an object out of the registry.
     * If it is not in the registry, it adds an new instance of the class to the registry.
     * @throws ReflectionException
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
     * @throws ReflectionException
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
     * Checks if Service exists in the registry
     */
    public function has(string $className): bool
    {
        return array_key_exists($className, $this->registry);
    }

    /**
     * Returns an Configuration item
     */
    public function getSetting(string $setting)
    {
        return $this->configuration[$setting];
    }
}