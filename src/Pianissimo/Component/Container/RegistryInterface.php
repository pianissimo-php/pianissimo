<?php

namespace App\Pianissimo\Component\Container;

interface RegistryInterface
{
    public function get(string $name);

    public function has(string $className): bool;
}