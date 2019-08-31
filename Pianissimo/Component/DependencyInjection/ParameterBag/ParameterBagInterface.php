<?php

namespace Pianissimo\Component\DependencyInjection\ParameterBag;

use IteratorAggregate;

interface ParameterBagInterface extends IteratorAggregate
{
    public function get(string $name);

    public function set(string $name, $value);
}
