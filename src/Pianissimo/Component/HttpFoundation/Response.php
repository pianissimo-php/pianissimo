<?php

namespace App\Pianissimo\Component\HttpFoundation;

class Response
{
    /** @var string */
    private $content;

    /** @var int */
    private $statusCode;

    public function __construct(string $content, int $statusCode = 200)
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}