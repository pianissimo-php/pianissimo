<?php

namespace Pianissimo\Component\Framework;

use Pianissimo\Component\HttpFoundation\Response;
use ReflectionClass;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class PianoTuner
{
    public static function render(Response $response, float $startTime): void
    {
        $reflectionClass = new ReflectionClass($response->getControllerClass());
        $controllerInfo = $reflectionClass->getShortName() . '::' . $response->getControllerMethod();
        $originInfo = $response->getRoute() ? $response->getRoute()->getName() : $controllerInfo;

        $executionTime = round((microtime(true) - $startTime) * 1000);
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? '-';

        // Generate a hash to prevent outside manipulation/conflicts of/with the PianoTuner elements
        $hashToolbar = base_convert(md5(random_int(0,999)), 10, 36);
        $hashFunction = '_' . base_convert(md5(random_int(0,999)), 10, 36);

        $loader = new FilesystemLoader(__DIR__);
        $twig = new Environment($loader);

        echo $twig->render('templates/piano_tuner.html.twig', [
            'response' => $response,
            'originInfo' => $originInfo,
            'toolbarId' => 'toolbar_' . $hashToolbar,
            'hashFunction' => $hashFunction,
            'executionTime' => $executionTime,
            'requestMethod' => $requestMethod,
        ]);
    }
}
