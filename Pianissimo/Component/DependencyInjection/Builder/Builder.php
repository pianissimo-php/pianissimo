<?php

namespace Pianissimo\Component\DependencyInjection\Builder;

use App\Manager\EntityManagerInterface;
use BadMethodCallException;
use InvalidArgumentException;
use LogicException;
use Pianissimo\Component\DependencyInjection\ContainerBuilder;
use Pianissimo\Component\DependencyInjection\ContainerInterface;
use Pianissimo\Component\DependencyInjection\Definition;
use Pianissimo\Component\DependencyInjection\DefinitionType;
use Pianissimo\Component\DependencyInjection\Exception\ClassNotFoundException;
use Pianissimo\Component\DependencyInjection\Exception\ContainerException;
use Pianissimo\Component\DependencyInjection\Reference;
use Pianissimo\Component\DependencyInjection\Value;
use ReflectionClass;
use ReflectionException;

class Builder
{
    /**
     * @var bool
     */
    private $autowireByDefault;

    /**
     * @var string[]|array
     */
    private $serviceIds = [];

    /**
     * Save ReflectionClasses to prevent initialization of multiple ReflectionClasses for the same class
     * @var ReflectionClass[]|array
     */
    private $reflectionClasses = [];

    /**
     * @var DefinitionType[]|array
     */
    private $definitions = [];

    /**
     * @var object[]|array
     */
    private $services = [];

    public function build(ContainerBuilder $containerBuilder, bool $autowireByDefault = false): Build
    {
        $this->autowireByDefault = $autowireByDefault;

        $definitions = $containerBuilder->getDefinitions();

        $this->registerServiceIds($definitions);
        $this->buildDefinitions($definitions);
        $this->initializeDefinitions();

        unset($this->reflectionClasses);

        return new Build($this->services, $this->definitions, $this->serviceIds);
    }

    private function registerServiceIds(array $definitions): void
    {
        foreach ($definitions as $id => $definition) {
            if ($definition instanceof Definition) {
                $this->registerServiceId($id, $definition->getClass());
                continue;
            }
            if ($definition instanceof Reference) {
                $this->serviceIds[$id] = $id;
                continue;
            }
        }
    }

    private function registerServiceId(string $id, string $value): void
    {
        if (in_array($id, $this->serviceIds, true)) {
            throw new BadMethodCallException(sprintf("Service definition with id '%s' already exists", $id));
        }

        $this->serviceIds[$value] = $id;
    }

    private function buildDefinitions(array $definitions): void
    {
        foreach ($definitions as $id => $definition) {
            $this->definitions[$id] = $this->buildDefinition($id, $definition);
        }
    }

    private function buildDefinition(string $id, DefinitionType $definition): DefinitionType
    {
        if ($definition instanceof Reference) {
            return $definition;
        }

        $class = $definition->getClass();

        if (class_exists($class) === false) {
            throw new ClassNotFoundException(sprintf("Not able to auto wire class '%s', class does not exist.", $class));
        }

        if ($definition->isAutowired() === true) {
            $definition = $this->autowireDefinition($definition);
        }

        return $definition;
    }

    private function autowireDefinition(Definition $definition): Definition
    {
        $class = $definition->getClass();

        try {
            $reflectionClass = $this->getReflectionClass($class);
        } catch (ReflectionException $e) {
            throw new ClassNotFoundException(sprintf("Not able to auto wire class '%s', class does not exist.", $class));
        }

        $constructor = $reflectionClass->getConstructor();

        if ($constructor === null) {
            // Nothing to autowire
            return $definition;
        }

        $parameters = $constructor->getParameters();

        $definitionArguments = [];

        foreach ($parameters as $parameter) {
            $name = $parameter->getName();

            $reflectionType = $parameter->getType();
            if ($reflectionType === null) {
                throw new InvalidArgumentException(sprintf("Cannot wire parameter '%s' with no type", $name));
            }

            $type = $reflectionType->getName();

            if (class_exists($type) === false && interface_exists($type) === false) {
                throw new InvalidArgumentException(sprintf("Cannot wire argument '%s' with type '%s'", $name, $type));
            }

            try {
                $parameterReflectionClass = $this->getReflectionClass($type);
            } catch (ReflectionException $e) {
                throw new ClassNotFoundException(sprintf("Not able to auto wire parameter '%s', class '%s' does not exist.", $name, $type));
            }

            if ($parameterReflectionClass->implementsInterface(ContainerInterface::class) === true) {
                throw new LogicException('Not allowed to inject the container');
            }

            if (array_key_exists($type, $this->serviceIds)) {
                $definitionArguments[] = new Reference($type);
                continue;
            }

            $definitionArguments[] = new Reference($type);
            $this->buildNewDefinition($type);
        }

        $definition->setArguments($definitionArguments);

        return $definition;
    }

    private function buildNewDefinition(string $class): Definition
    {
        $definition = new Definition($class);
        $this->registerNewDefinition($definition);

        if ($this->autowireByDefault === true) {
            $this->autowireDefinition($definition);

            return $definition;
        }

        throw new InvalidArgumentException(sprintf("Error: unable to inject class '%s': there is no service definition found and autowiring is disabled", $class));
    }

    private function registerNewDefinition(Definition $definition): void
    {
        $id = $class = $definition->getClass();

        $this->serviceIds[$class] = $id;
        $this->definitions[$id] = $definition;
    }

    private function initializeDefinitions(): void
    {
        $definitions = $this->definitions;

        foreach ($definitions as $serviceId => $definition) {
            $this->initializeDefinition($serviceId, $definition);
        }
    }

    private function initializeDefinition(string $serviceId, DefinitionType $definition): object
    {
        if ($definition instanceof Reference) {
            return $this->initializeDefinition($serviceId, $this->resolveReference($definition));
        }

        $arguments = [];

        foreach ($definition->getArguments() as $key => $argument) {
            $argumentDefinition = null;

            if ($argument instanceof Reference) {
                $argumentDefinition = $this->resolveReference($argument);
            }

            if ($argument instanceof Definition) {
                $argumentDefinition = $argument;
            }

            if ($argument instanceof Value) {
                $arguments[] = (string) $argument;
                continue;
            }

            if ($argumentDefinition === null) {
                throw new InvalidArgumentException(sprintf("Argument '%s' with type '%s' is not a valid argument type to wire", $argument, gettype($argument)));
            }

            $argumentServiceId = $this->getServiceId($argumentDefinition->getClass());

            if ($this->hasService($argumentServiceId)) {
                $arguments[] = $this->getService($argumentServiceId);
                continue;
            }

            $arguments[] = $this->initializeDefinition($argumentDefinition->getClass(), $argumentDefinition);
        }

        $class = $definition->getClass();

        $definitionReflectionClass = $this->getReflectionClass($class);
        $constructor = $definitionReflectionClass->getConstructor();

        if ($constructor && count($arguments) < $constructor->getNumberOfRequiredParameters()) {
            throw new ContainerException(sprintf("Invalid service definition for class '%s'", $class));
        }

        $instance = new $class(...$arguments);
        $this->services[$serviceId] = $instance;

        return $instance;
    }

    private function resolveReference(Reference $reference): Definition
    {
        $id = (string) $reference;

        if (array_key_exists($id, $this->definitions) === false) {
            throw new InvalidArgumentException(sprintf("Definition with id '%s' does not exists", $id));
        }

        $match = $this->definitions[$id];

        if ($match instanceof Definition) {
            return $match;
        }

        if ($match instanceof Reference) {
            return $this->resolveReference($match);
        }

        throw new LogicException('Unknown type');
    }

    /**
     * @throws ReflectionException
     */
    private function getReflectionClass(string $class): ReflectionClass
    {
        if (array_key_exists($class, $this->reflectionClasses) === false) {
            $this->reflectionClasses[$class] = new ReflectionClass($class);
        }

        return $this->reflectionClasses[$class];
    }

    private function getServiceId(string $class): string
    {
        if (array_key_exists($class, $this->serviceIds)) {
            return $this->serviceIds[$class];
        }

        return $class;
    }

    private function hasService(string $id): bool
    {
        return array_key_exists($id, $this->services);
    }

    private function getService(string $id): object
    {
        return $this->services[$id];
    }
}
