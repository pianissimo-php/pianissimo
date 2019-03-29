<?php

use App\Pianissimo\Container;
use App\Pianissimo\Environment;
use App\Pianissimo\PianoEngine;

require '../vendor/autoload.php';

const ENV = 'dev';
$environment = new Environment();

function dump($var):void
{
    echo '<pre style="border: 2px solid black; padding: 20px;">';
    print_r($var);
    echo '</pre>';
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

$container = new Container();

$pianoEngine = $container->get(PianoEngine::class);
$pianoEngine->start();