<?php

namespace Pianissimo\Component\DependencyInjection;

/**
 * This class holds a reference to a service definition.
 */
class Reference
{
    /**
     * @var string
     */
    private $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function __toString(): string
    {
        return $this->id;
    }
}
