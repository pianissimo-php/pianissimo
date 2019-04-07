<?php

namespace App\Pianissimo\Component\Container;

class ConfigurationRegistry implements RegistryInterface
{
    /** @var array */
    private $registry;

    public function initialize(array $data): void
    {
        $this->registry = $data;
    }

    public function get(string $name)
    {
        if (isset($this->registry[$name]) === true) {
            return $this->registry[$name];
        }

        return null;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->registry);
    }
}