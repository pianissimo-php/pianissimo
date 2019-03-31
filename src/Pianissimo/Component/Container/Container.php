<?php

namespace App\Pianissimo\Component\Container;

use App\Pianissimo\Component\Configuration\Configuration;
use App\Pianissimo\Component\Configuration\ConfigurationService;
use App\Pianissimo\Component\Configuration\Exception\ConfigurationFileException;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class Container
{
    /** @var array */
    private static $registry;

    /** @var Configuration */
    private $configuration;

    /**
     * Initialize the Container
     * @throws ConfigurationFileException
     */
    public function __construct()
    {
        // Load ConfigurationService manually, handler hasn't to be available in de service container
        $configurationService = new ConfigurationService();
        $this->configuration = $configurationService->load();
    }

    /**
     * This functions gets an object out of the registry.
     * If it is not in the registry, it adds an new instance of the class to the registry.
     */
    public function get(string $className)
    {
        if (isset(self::$registry[$className]) === false) {
            $this->set($className);
        }

        return self::$registry[$className];
    }

    /**
     * This function creates an new instance of the given class name and adds it to the registry.
     * It will also auto wire the parameters of the new instance. (Dependency Injection)
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

        if ($parentClass !== false) {
            $parentInstance = $this->get($parentClass->getName());
        }

        $parameters = [];

        if ($constructor !== null) {
            $autoWiredParameters = $this->autoWireMethod($constructor);
            $parameters = array_merge($parameters, $autoWiredParameters);
        }

        $instance = new $className(...$parameters);

        self::$registry[$className] = $instance;
    }

    public function has(string $className): bool
    {
        return array_key_exists($className, self::$registry);
    }

    public function getSetting(string $setting)
    {
        return $this->configuration->get($setting);
    }

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
}