<?php

namespace Pianissimo\Component\HttpFoundation;

use Pianissimo\Component\Routing\Route;

class Response
{
    /** @var string */
    private $content;

    /** @var int */
    private $statusCode;

    /** @var string */
    private $controllerClass;

    /** @var string */
    private $controllerFunction;

    /** @var Route|null */
    private $route;

    /** @var bool */
    private $isHtml;

    public function __construct(string $content, int $statusCode = 200)
    {
        $this->content = $content;
        $this->controllerClass = debug_backtrace()[1]['class'];
        $this->controllerFunction = debug_backtrace()[1]['function'];
        $this->statusCode = $statusCode;
        $this->isRendered = false;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getRoute(): ?Route
    {
        return $this->route;
    }

    public function getControllerClass(): string
    {
        return $this->controllerClass;
    }

    public function getControllerFunction(): string
    {
        return $this->controllerFunction;
    }

    public function isRendered(): bool
    {
        return $this->isRendered;
    }

    public function setRoute(Route $route): self
    {
        $this->route = $route;
        return $this;
    }

    public function setRendered(bool $isRendered): void
    {
        $this->isRendered = $isRendered;
    }
}