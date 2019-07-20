<?php

namespace Pianissimo\Component\DependencyInjection\ParameterBag;

interface ParameterBagInterface
{
    public function get(string $name);

    public function set(string $name, $value);
}
