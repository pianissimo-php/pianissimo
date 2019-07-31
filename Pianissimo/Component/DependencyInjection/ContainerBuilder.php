<?php

namespace Pianissimo\Component\DependencyInjection;

use InvalidArgumentException;
use LogicException;
use Pianissimo\Component\DependencyInjection\Builder\Builder;
use Pianissimo\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Pianissimo\Component\DependencyInjection\Exception\ContainerException;
use Pianissimo\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Pianissimo\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ContainerBuilder extends Container
{
    /**
     * @var DefinitionType[]|array
     */
    private $definitions = [];

    /**
     * @var string[]|array
     */
    private $serviceIds = [];

    /**
     * @var bool
     */
    private $built;

    /**
     * @var CompilerPassInterface[]|array
     */
    private $compilerPasses = [];

    public function __construct(ParameterBagInterface $parameterBag = null)
    {
        parent::__construct($parameterBag);
    }

    public function getParameter(string $name)
    {
        return $this->parameterBag->get($name);
    }

    public function setParameter($name, $value): void
    {
        $this->parameterBag->set($name, $value);
    }

    public function add(string $id, DefinitionType $definitionType): void
    {
        $this->setDefinition($id, $definitionType);
    }

    public function register(string $id, string $class): Definition
    {
        return $this->setDefinition($id, new Definition($class));
    }

    public function hasDefinition(string $id): bool
    {
        return array_key_exists($id, $this->definitions);
    }

    public function getDefinitions(): array
    {
        return $this->definitions;
    }

    private function setDefinition(string $id, DefinitionType $definitionType): DefinitionType
    {
        return $this->definitions[$id] = $definitionType;
    }

    public function has($id): bool
    {
        $serviceId = $this->getServiceId($id);

        return parent::has($serviceId);
    }

    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function get($id): object
    {
        if ($this->built === false) {
            throw new ContainerException('The container has not yet been built');
        }

        $serviceId = $this->getServiceId($id);

        if ($this->has($serviceId)) {
            return parent::get($serviceId);
        }

        return $this->load($serviceId);
    }

    public function build(): void
    {
        if ($this->built === true) {
            throw new ContainerException('The container has already been built');
        }

        $builder = new Builder();
        $build = $builder->build($this, true);

        $this->definitions = $build->getDefinitions();
        $this->serviceIds = $build->getServiceIds();

        $this->handleCompilerPasses();

        $this->built = true;
    }

    private function getServiceId(string $id): string
    {
        if (array_key_exists($id, $this->serviceIds)) {
            return $this->serviceIds[$id];
        }

        return $id;
    }

    private function createService(string $serviceId, DefinitionType $definition): object
    {
        if ($definition instanceof Reference) {
            return $this->createService($serviceId, $this->resolveReference($definition));
        }

        if (!$definition instanceof Definition) {
            throw new LogicException('Unhandled type: TODO');
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

            if (is_string($argument) || is_float($argument) || is_int($argument)) {
                $arguments[] = $argument;
                continue;
            }

            if ($argumentDefinition === null) {
                throw new InvalidArgumentException(sprintf("Argument '%s' with type '%s' is not a valid argument type to wire", $argument, gettype($argument)));
            }

            $argumentServiceId = $this->getServiceId($argumentDefinition->getClass());

            if ($this->has($argumentServiceId)) {
                $arguments[] = $this->get($argumentServiceId);
                continue;
            }

            $arguments[] = $this->get($argumentServiceId);
        }

        $class = $definition->getClass();

        $instance = new $class(...$arguments);
        $this->services[$serviceId] = $instance;

        return $instance;
    }

    private function resolveReference(Reference $reference): Definition
    {
        $id = $this->getServiceId((string) $reference);

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

    public function findServicesByTag(string $tag): array
    {
        $taggedServices = [];

        foreach ($this->definitions as $definition) {
            if ($definition instanceof Reference) {
                $definition = $this->resolveReference($definition);
            }
            if ($definition->hasTag($tag) === true) {
                $taggedServices[] = $definition;
            }
        }

        return $taggedServices;
    }

    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function load(string $id): object
    {
        $serviceId = $this->getServiceId($id);

        if ($this->hasDefinition($serviceId) === false) {
            throw new ServiceNotFoundException(sprintf("Service '%s' not found", $serviceId));
        }

        $definition = $this->definitions[$serviceId];

        if (!$definition instanceof Definition) {
            throw new ContainerException(sprintf("Can not load service '%s' of type '%s'", $id, getType($definition)));
        }

        $instance = $this->services[$serviceId] = $this->createService($serviceId, $definition);

        foreach ($definition->getMethodCalls() as $methodCall) {
            $methodName = $methodCall->getMethod();

            $instance->$methodName(...$methodCall->getArguments());
        }

        return $instance;
    }

    public function addCompilerPass(CompilerPassInterface $compilerPass): self
    {
        $this->compilerPasses[] = $compilerPass;

        return $this;
    }

    private function handleCompilerPasses(): self
    {
        foreach ($this->compilerPasses as $compilerPass) {
            $compilerPass->process($this);
        }

        return $this;
    }
}
