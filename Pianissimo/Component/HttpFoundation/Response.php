<?php

namespace Pianissimo\Component\HttpFoundation;

use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Pianissimo\Component\Routing\Route;

class Response extends GuzzleResponse
{
    /**
     * @var Route|null
     */
    private $route;

    /**
     * @var string
     */
    private $controllerClass;

    /**
     * @var string
     */
    private $controllerFunction;

    public function __construct($body = null, int $status = 200, array $headers = [], string $version = '1.1', ?string $reason = null)
    {
        parent::__construct($status, $headers, $body, $version, $reason);

        $this->controllerClass = debug_backtrace()[1]['class'];
        $this->controllerFunction = debug_backtrace()[1]['function'];
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

    public function setRoute(Route $route): self
    {
        $this->route = $route;

        return $this;
    }
}
