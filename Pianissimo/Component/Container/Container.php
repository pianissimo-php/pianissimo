<?php

namespace Pianissimo\Component\Container;

use Pianissimo\Component\Container\Exception\ClassNotFoundException;
use Pianissimo\Component\Core\ContainerInterface;
use Pianissimo\Component\Core\RegistryInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * The Container holds the registry with initialized Services and the initialized Configuration.
 */
class Container implements ContainerInterface, RegistryInterface
{
    /** @var array */
    private $registry;

    /** @var ConfigurationRegistry */
    private $configurationRegistry;

    /**
     * Initialize the Container
     */
    public function __construct()
    {
        // Initialize registries.
        $this->registry = [];
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
     * @throws ClassNotFoundException
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
     * @throws ClassNotFoundException
     */
    private function set(string $className): void
    {
        // The heart of the Dependency Injection.
        try {
            $class = new ReflectionClass($className);
        } catch (ReflectionException $e) {
            throw new ClassNotFoundException(sprintf("Not able to auto wire class '%s', class does not exist.", $className));
        }

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
            try {
                $parameterClass = $parameter->getClass()->getName();
                $autoWiredParameters[] = $this->get($parameterClass);
            } catch (ReflectionException $e) {
                throw new ClassNotFoundException($e->getMessage());
            }
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