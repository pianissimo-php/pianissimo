<?php

namespace Pianissimo\Component\HttpFoundation;

use GuzzleHttp\Psr7\LazyOpenStream;
use GuzzleHttp\Psr7\ServerRequest;

class Request extends ServerRequest
{
    /**
     * Return a ServerRequest populated with super globals:
     * $_GET
     * $_POST
     * $_COOKIE
     * $_FILES
     * $_SERVER
     *
     * @return Request
     */
    public static function fromGlobals(): Request
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $headers = getallheaders();
        $uri = self::getUriFromGlobals();
        $body = new LazyOpenStream('php://input', 'r+');
        $protocol = isset($_SERVER['SERVER_PROTOCOL']) ? str_replace('HTTP/', '', $_SERVER['SERVER_PROTOCOL']) : '1.1';

        $serverRequest = new self($method, $uri, $headers, $body, $protocol, $_SERVER);

        return $serverRequest
            ->withCookieParams($_COOKIE)
            ->withQueryParams($_GET)
            ->withParsedBody($_POST)
            ->withUploadedFiles(self::normalizeFiles($_FILES));
    }
}