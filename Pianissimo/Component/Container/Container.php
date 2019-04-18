<?php

namespace Pianissimo\Component\Container;

use Pianissimo\Component\Container\Exception\ClassNotFoundException;
use Pianissimo\Component\Container\Exception\ContainerException;
use Pianissimo\Component\Container\Exception\ServiceNotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class Container implements ContainerInterface
{
    /** @var array */
    private $serviceRegistry;

    /** @var ConfigurationRegistry */
    private $configurationRegistry;

    public function __construct()
    {
        $this->serviceRegistry = [];

        // Inject this instance of the container in the registry
        $this->serviceRegistry[__CLASS__] = $this;

        $this->configurationRegistry = new ConfigurationRegistry();

        // Load ConfigurationHandler manually, handler hasn't to be available in de service container
        $configurationHandler = new ConfigurationHandler();
        $this->configurationRegistry->initialize($configurationHandler->load());
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get($id)
    {
        if ($this->has($id) === true) {
            return $this->serviceRegistry[$id];
        }

        throw new ServiceNotFoundException(sprintf("No entry was found for identifier '%s'", $id));
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     *
     */
    public function has($id): bool
    {
        return isset($this->serviceRegistry[$id]) === true;
    }

    /**
     * @throws ContainerException
     */
    public function set($id, $service)
    {
        if ($this->has($id) === false) {
            $this->serviceRegistry[$id] = $service;
        } else {
            throw new ContainerException(sprintf("Service with id '%s' already exists", $id));
        }

        return $service;
    }

    /**
     * @throws ClassNotFoundException
     * @throws ContainerException
     */
    public function autowire(string $className)
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

        return $this->set($className, $instance);
    }

    /**
     * Auto wires the parameters of the given ReflectionMethod.
     * @throws ClassNotFoundException
     * @throws ContainerException
     */
    private function autoWireMethod(ReflectionMethod $method): array
    {
        $parameters = $method->getParameters();

        $autoWiredParameters = [];

        foreach ($parameters as $parameter) {
            try {
                $parameterClass = $parameter->getClass()->getName();
                if ($this->has($parameterClass)) {
                    $autoWiredParameters[] = $this->get($parameterClass);
                } else {
                    $autoWiredParameters[] = $this->autowire($parameterClass);
                }
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