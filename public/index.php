<?php

use Pianissimo\Component\Cache\CacheService;
use Pianissimo\Component\Core\HttpCore;
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

$core = new HttpCore();

$request = Request::fromGlobals();
$response = $core->handleRequest($request);
$core->handleResponse($response);

/*
$core = new HttpCore();
$request = new Request();

$response = $core->handleRequest($request);
$core->handleResponse($response);
*/