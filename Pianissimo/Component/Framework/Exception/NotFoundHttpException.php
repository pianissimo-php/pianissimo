<?php

namespace Pianissimo\Component\Framework\Exception;

use Exception;
use Throwable;

class NotFoundHttpException extends Exception
{
    public function __construct(string $message = '', int $code = 404, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
