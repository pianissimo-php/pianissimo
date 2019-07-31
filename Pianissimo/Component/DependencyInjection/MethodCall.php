<?php

namespace Pianissimo\Component\DependencyInjection;

class MethodCall
{
    /**
     * @var string
     */
    private $method;

    /**
     * @var array
     */
    private $arguments;

    public function __construct(string $method, array $arguments = [])
    {
        $this->method = $method;
        $this->arguments = $arguments;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }
}
