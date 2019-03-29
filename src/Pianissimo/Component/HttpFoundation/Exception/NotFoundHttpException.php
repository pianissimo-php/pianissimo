<?php

namespace App\Pianissimo\Component\HttpFoundation\Exception;

use Throwable;

class NotFoundHttpException extends \Exception
{
    public function __construct(string $message = '', int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}