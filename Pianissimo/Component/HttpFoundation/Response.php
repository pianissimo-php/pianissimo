<?php

namespace Pianissimo\Component\HttpFoundation;

use GuzzleHttp\Psr7\Response as GuzzleResponse;

class Response extends GuzzleResponse
{
    public function __construct(int $status = 200, array $headers = [], $body = null, string $version = '1.1', ?string $reason = null)
    {
        parent::__construct($status, $headers, $body, $version, $reason);
    }
}