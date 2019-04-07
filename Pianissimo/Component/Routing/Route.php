<?php

namespace Pianissimo\Component\Routing;

class Route
{
    /** @var string */
    private $class;

    /** @var string */
    private $function;

    /** @var string */
    private $path;

    /** @var string|null */
    private $name;

    public function __construct(string $class, string $function, string $path, ?string $name = null)
    {
        $this->class = $class;
        $this->function = $function;
        $this->path = $path;
        $this->name = $name;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getFunction(): string
    {
        return $this->function;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}