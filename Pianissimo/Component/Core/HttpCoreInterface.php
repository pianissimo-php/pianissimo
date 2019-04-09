<?php

namespace Pianissimo\Component\Core;

use Pianissimo\Component\HttpFoundation\Request;
use Pianissimo\Component\HttpFoundation\Response;

/**
 * A HttpCore should be able to handle Requests and Responses.
 */
interface HttpCoreInterface extends CoreInterface
{
    public function handleRequest(Request $request): Response;

    public function handleResponse(Response $response);
}