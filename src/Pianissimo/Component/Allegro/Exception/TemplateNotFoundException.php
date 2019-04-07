<?php

namespace App\Pianissimo\Component\Allegro\Exception;

use Exception;
use Throwable;

class TemplateNotFoundException extends Exception
{
    public function __construct(string $message = '', int $code = 404, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}