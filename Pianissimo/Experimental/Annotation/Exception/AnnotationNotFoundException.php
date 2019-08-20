<?php

namespace Pianissimo\Experimental\Annotation\Exception;

use Exception;
use Throwable;

class AnnotationNotFoundException extends Exception
{
    public function __construct(string $message = '', int $code = 500, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
