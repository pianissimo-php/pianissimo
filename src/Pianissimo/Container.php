<?php

namespace App\Pianissimo;

use ReflectionClass;

class Container
{
    /** @var array */
    private $registry;

    /**
     * This functions gets an object out of the registry.
     * If it is not in the registry, it adds an new instance of the class to the registry.
     */
    public function get(string $className)
    {
        if (isset($this->register[$className]) === false) {
            $this->set($className);
        }

        return $this->registry[$className];
    }

    /**
     * This function creates an new instance of the given class name and adds it to the registry.
     * It will also auto wire the parameters of the new instance. (Dependency Injection)
     */
    private function set(string $className): void
    {
        $newInstanceParameters = [];

        // The heart of the Dependency Injection.
        $class = new ReflectionClass($className);
        $constructor = $class->getConstructor();

        if ($constructor !== null) {
            $parameters = $constructor->getParameters();

            foreach ($parameters as $parameter) {
                $parameterClass = $parameter->getClass()->getName();
                $newInstanceParameters[] = $this->get($parameterClass);
            }
        }

        $this->registry[$className] = new $className(...$newInstanceParameters);
    }
}