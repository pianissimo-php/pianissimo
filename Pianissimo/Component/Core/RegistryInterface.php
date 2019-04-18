<?php

namespace Pianissimo\Component\Core;

interface RegistryInterface
{
    public function get(string $name);

    public function has(string $name): bool;
}