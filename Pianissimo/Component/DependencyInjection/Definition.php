<?php

namespace Pianissimo\Component\DependencyInjection;

class Definition extends DefinitionType
{
    /**
     * @var string|null
     */
    private $class;

    /**
     * @var boolean
     */
    private $autowired;

    /**
     * @var array
     */
    private $arguments;

    public function __construct(string $class, array $arguments = [])
    {
        $this->class = $class;
        $this->autowired = false;
        $this->arguments = $arguments;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function isAutowired(): bool
    {
        return $this->autowired;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function setClass(string $class): self
    {
        $this->class = $class;

        return $this;
    }

    public function setAutowired(bool $autowired): self
    {
        $this->autowired = $autowired;

        return $this;
    }

    public function addArgument($value): self
    {
        $this->arguments[] = $value;

        return $this;
    }

    public function setArguments(array $arguments): self
    {
        $this->arguments = $arguments;

        return $this;
    }
}
