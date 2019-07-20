<?php

namespace Pianissimo\Component\DependencyInjection;

use Pianissimo\Component\DependencyInjection\Builder\Builder;
use Pianissimo\Component\DependencyInjection\Exception\ContainerException;
use Pianissimo\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ContainerBuilder extends Container
{
    /**
     * @var Definition[]|array
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

    public function register(string $id, $class = null): Definition
    {
        return $this->setDefinition($id, new Definition($class));
    }

    public function getDefinitions()
    {
        return $this->definitions;
    }

    private function setDefinition(string $id, Definition $serviceDefinition): Definition
    {
        return $this->definitions[$id] = $serviceDefinition;
    }

    public function has($id): bool
    {
        $serviceId = $this->getServiceId($id);

        return parent::has($serviceId);
    }

    public function get($id): object
    {
        $serviceId = $this->getServiceId($id);

        return parent::get($serviceId);
    }

    public function build(): void
    {
        if ($this->built === true) {
            throw new ContainerException('Container already built');
        }

        $builder = new Builder();
        $build = $builder->build($this, true);

        $this->services = $build->getServices();
        $this->definitions = $build->getDefinitions();
        $this->serviceIds = $build->getServiceIds();

        $this->built = true;
    }

    private function getServiceId(string $id)
    {
        if (array_key_exists($id, $this->serviceIds)) {
            return $this->serviceIds[$id];
        }
    }
}
