<?php

use Pianissimo\Component\Framework\Core;
use Pianissimo\Component\HttpFoundation\Request;

require __DIR__ . '/../vendor/autoload.php';

function dump($var, $return = false)
{
    $dump = '<pre style="border: 2px solid black; padding: 20px;">' . print_r($var, true) . '</pre>';

    if ($return === true) {
        return $dump;
    }
    echo $dump;
}

function dd($var):void
{
    dump($var);
    die;
}

$core = new Core('dev', true);

$request = Request::fromGlobals();
$response = $core->handle($request);
$core->send($response);
