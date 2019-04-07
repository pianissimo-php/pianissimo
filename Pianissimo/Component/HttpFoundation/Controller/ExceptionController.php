<?php

namespace Pianissimo\Component\HttpFoundation\Controller;

use Pianissimo\Component\HttpFoundation\Response;
use ReflectionClass;
use Throwable;

class ExceptionController
{
    public function index(Throwable $exception): Response
    {
        $css = '
            background-color: #eb4d4b;
            color: white;
            font-size: 1.6em;
            text-align: center;
            font-family: verdana;
            padding: 50px 0px;
            line-height: 1.5em;
        ';

        $exceptionName = (new ReflectionClass($exception))->getShortName();

        /*
        $trace = debug_backtrace();
        foreach ($trace as $traceItem) {
            dump($traceItem['class'] . '::' . $traceItem['function']);
        }
        */

        $content = '
            <html>
            <head>
                <title>Pianissimo</title>
            </head>
            <body style="margin: 0;">
                <div style="' . $css . '">' . $exceptionName . ': ' . $exception->getMessage() . '<br />
                <small>in ' . $exception->getFile() . ' on line ' . $exception->getLine() . '</small>
                </div>
            </body>
            </html>
        ';

        return new Response($content, $exception->getCode());
    }
}