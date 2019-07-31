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
     * @var mixed[]|array
     */
    private $arguments;

    /**
     * @var string[]|array
     */
    private $tags;

    /**
     * @var MethodCall|array
     */
    private $methodCalls;

    public function __construct(string $class, array $arguments = [])
    {
        $this->class = $class;
        $this->autowired = false;
        $this->arguments = $arguments;
        $this->tags = [];
        $this->methodCalls = [];
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

    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags, true);
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function addTag(string $tag): self
    {
        $this->tags[] = $tag;

        return $this;
    }

    /**
     * @return array|MethodCall[]
     */
    public function getMethodCalls(): array
    {
        return $this->methodCalls;
    }

    public function addMethodCall(string $methodName, array $arguments = []): self
    {
        $this->methodCalls[] = new MethodCall($methodName, $arguments);

        return $this;
    }
}
