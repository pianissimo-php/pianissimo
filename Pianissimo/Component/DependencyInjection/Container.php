<?php

namespace Pianissimo\Component\DependencyInjection;

use Pianissimo\Component\DependencyInjection\ParameterBag\ParameterBag;
use Pianissimo\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class Container implements ContainerInterface
{
    /**
     * @var ParameterBagInterface
     */
    protected $parameterBag;

    /**
     * @var object[]|array
     */
    protected $services;

    public function __construct(ParameterBagInterface $parameterBag = null)
    {
        $this->services = [];

        // Initialize Pianissimo's ParameterBag if none is provided
        $this->parameterBag = $parameterBag ?: new ParameterBag();

        // Register the ParameterBag as a service
        $this->services[ParameterBagInterface::class] = $this->parameterBag;
    }

    public function getParameter(string $name)
    {
        return $this->parameterBag->get($name);
    }

    public function setParameter($name, $value): self
    {
        $this->parameterBag->set($name, $value);

        return $this;
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
        return $this->services[$id];
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
        return array_key_exists($id, $this->services);
    }
}
