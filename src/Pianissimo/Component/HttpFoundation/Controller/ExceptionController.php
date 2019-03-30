<?php

namespace App\Pianissimo\Component\HttpFoundation\Controller;

use App\Pianissimo\Component\HttpFoundation\Response;
use ReflectionClass;
use ReflectionException;
use Throwable;

class ExceptionController
{
    /**
     * @throws ReflectionException
     */
    public function index(Throwable $exception): Response
    {
        $css = '
            background-color: #e74c3c;
            color: white;
            font-size: 1.6em;
            text-align: center;
            font-family: verdana;
            padding: 50px 0px;
            line-height: 1.5em;
        ';

        $exceptionName = (new ReflectionClass($exception))->getShortName();

        $content =
            '<div style="' . $css . '">' .
            $exceptionName . ': ' . $exception->getMessage() . '<br />' .
            '<small>in ' . $exception->getFile() . ' on line ' . $exception->getLine() . '</small>' .
            '</div>';

        return new Response($content);
    }
}