<?php

namespace Pianissimo\Component\Framework\Controller;

use Pianissimo\Component\HttpFoundation\Response;

class ErrorController
{
    public function index(int $errorNo, string $errorString, string $errorFile, int $errorLine): Response
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

        $content =
            '<div style="' . $css . '">
            Error: ' . $errorString . '<br />' .
            '<small>in ' . $errorFile . ' on line ' . $errorLine . '</small>' .
            '</div>';

        return new Response($content, 500);
    }
}
