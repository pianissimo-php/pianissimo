<?php

namespace Pianissimo\Component\DependencyInjection;

use Pianissimo\Component\DependencyInjection\Builder\Builder;
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

    public function __construct(ParameterBagInterface $parameterBag = null)
    {
        parent::__construct($parameterBag);
    }

    public function getParameter(string $name)
    {
        return $this->parameterBag->get($name);
    }

    public function setParameter($name, $value)
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

    public function get($id): object
    {
        $serviceId = $this->getServiceId($id);

        if ($this->has($id) === false) {
            throw new ServiceNotFoundException('Service not found');
        }

        return parent::get($serviceId);
    }

    public function build(): void
    {
        if ($this->built === true) {
            throw new ContainerException('The container has already been built');
        }

        $builder = new Builder();
        $build = $builder->build($this, true);

        $this->services = $build->getServices();
        $this->definitions = $build->getDefinitions();
        $this->serviceIds = $build->getServiceIds();

        $this->built = true;
    }

    private function getServiceId(string $id): string
    {
        if (array_key_exists($id, $this->serviceIds)) {
            return $this->serviceIds[$id];
        }

        return $id;
    }
}
