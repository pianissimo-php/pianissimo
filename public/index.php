<?php

use Pianissimo\Component\Container\Container;
use Pianissimo\Component\HttpFoundation\HttpService;
use Pianissimo\Component\HttpFoundation\Request;
use Pianissimo\Environment;

require '../vendor/autoload.php';

$container = new Container();
$environment = $container->get(Environment::class);

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

function getRootDirectory()
{
    return __DIR__ . '..';
}

function getProjectDirectory()
{
    return getRootDirectory() . '/src';
}

$httpService = $container->get(HttpService::class);

$request = new Request();
$response = $httpService->getResponse($request);
$httpService->handleResponse($response);