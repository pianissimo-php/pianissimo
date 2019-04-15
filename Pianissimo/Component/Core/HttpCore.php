<?php

namespace Pianissimo\Component\Core;

use Pianissimo\Component\HttpFoundation\Request;
use Pianissimo\Component\HttpFoundation\RequestHandler;
use Pianissimo\Component\HttpFoundation\Response;

class HttpCore extends Core
{
    /** @var RequestHandler */
    private $requestHandler;

    public function __construct()
    {
        parent::__construct();
        $this->requestHandler = $this->container->autowire(RequestHandler::class);
    }

    public function handle(Request $request): Response
    {
        return $this->requestHandler->handle($request);
    }
}