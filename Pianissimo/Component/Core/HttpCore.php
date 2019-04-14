<?php

namespace Pianissimo\Component\Core;

use Pianissimo\Component\Core\Container\Container;
use Pianissimo\Component\HttpFoundation\Request;
use Pianissimo\Component\HttpFoundation\RequestHandler;
use Pianissimo\Component\HttpFoundation\Response;

class HttpCore extends Core
{
    /** @var RequestHandler */
    private $requestHandler;

    public function __construct()
    {
        $container = new Container();
        $this->requestHandler = $container->autowire(RequestHandler::class);
    }

    public function handle(Request $request): Response
    {
        return $this->requestHandler->handle($request);
    }
}