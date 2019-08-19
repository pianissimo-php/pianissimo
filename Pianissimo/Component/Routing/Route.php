<?php

namespace Pianissimo\Component\Routing;

class Route
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string|null
     */
    private $name;

    public function __construct(
        string $class,
        string $method,
        string $path,
        ?string $name = null
    ) {
        $this->class = $class;
        $this->method = $method;
        $this->path = $path;
        $this->name = $name;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getMethod(): string
    {
        return $this->method;
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
