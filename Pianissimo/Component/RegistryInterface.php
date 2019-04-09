<?php

namespace Pianissimo\Component;

interface RegistryInterface
{
    public function get(string $name);

    public function has(string $className): bool;
}