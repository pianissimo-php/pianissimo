<?php

namespace Pianissimo\Component\DependencyInjection\ParameterBag;

class ParameterBag implements ParameterBagInterface
{
    /**
     * @var array
     */
    private $parameters;

    public function get($name)
    {
        return $this->parameters[$name];
    }

    public function set($name, $value)
    {
        $this->parameters[$name] = $value;
    }
}
