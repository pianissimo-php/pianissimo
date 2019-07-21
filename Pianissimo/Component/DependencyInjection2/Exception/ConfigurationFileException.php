<?php

namespace Pianissimo\Component\DependencyInjection2\Exception;

use Exception;
use Throwable;

class ConfigurationFileException extends Exception
{
    public function __construct(string $message = '', int $code = 500, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
