<?php

namespace App\Pianissimo;

class Environment
{
    public function __construct()
    {
        if (ENV === 'dev') {
            $this->setDebugMode(true);
        }
        if (ENV === 'prod') {
            $this->setDebugMode(false);
        }
    }

    private function setDebugMode(bool $mode): void
    {
        if ($mode === true) {
            set_error_handler([__CLASS__, 'errorHandler']);
            set_exception_handler([__CLASS__, 'exceptionHandler']);
        } else {
            ini_set('display_errors', 0);
            ini_set('log_errors', 1);
        }
    }

    public static function errorHandler($errno, $errstr, $error_file, $error_line): void
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

        echo
            '<div style="' . $css . '">
            Error: ' . $errstr . '<br />' .
            '<small>in ' . $error_file . ' on line ' . $error_line . '</small>' .
            '</div>';
        die();
    }

    public static function exceptionHandler($exception): void
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

        echo
            '<div style="' . $css . '">
            Uncaught exception: ' . $exception->getMessage() . '<br />' .
            '<small>in ' . $exception->getFile() . ' on line ' . $exception->getLine() . '</small>' .
            '</div>';
    }
}