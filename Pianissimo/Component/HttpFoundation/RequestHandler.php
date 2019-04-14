<?php

namespace Pianissimo\Component\HttpFoundation;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RequestHandler implements RequestHandlerInterface
{
    /**
     * Handles a request and produces a response.
     *
     * May call other collaborating code to generate the response.
     *
     * Each request wil go through this function. From this place, all services will be auto wired!
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new Response();
    }
}