<?php

namespace Pianissimo\Component\HttpFoundation;

class RedirectResponse extends Response
{
    public function __construct($redirectUrl = null, int $status = 302, array $headers = [], string $version = '1.1', ?string $reason = null)
    {
        $headers['location'] = $redirectUrl;

        parent::__construct(null, $status, $headers, $version, $reason);
    }
}