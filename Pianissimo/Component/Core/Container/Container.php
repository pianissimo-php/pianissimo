<?php

namespace Pianissimo\Component\Core\Container;

use Pianissimo\Component\Core\Container\Exception\ContainerException;
use Pianissimo\Component\Core\Container\Exception\NotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class Container implements ContainerInterface
{
    /** @var array */
    private $registry;

    public function __construct()
    {
        $this->registry = [];
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
            return $this->registry[$id];
        }

        throw new NotFoundException(sprintf("No entry was found for identifier '%s'", $id));
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
     * @return bool
     */
    public function has($id): bool
    {
        return isset($this->registry[$id]) === true;
    }

    public function set($id, $service)
    {
        if ($this->has($id) === false) {
            $this->registry[$id] = $service;
        } else {
            throw new ContainerException(sprintf("Service with id '%s' already exists", $id));
        }
    }

    public function autowire(string $class)
    {
        
    }
}