<?php

namespace App\Pianissimo\Component\HttpFoundation;

class RedirectResponse extends Response
{
    /** @var string */
    private $redirectUrl;

    public function __construct(string $redirectUrl, int $statusCode = 302)
    {
        parent::__construct('', $statusCode);
        $this->redirectUrl = $redirectUrl;
    }

    public function getRedirectUrl(): string
    {
        return $this->redirectUrl;
    }
}