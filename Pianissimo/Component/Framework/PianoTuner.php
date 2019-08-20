<?php

namespace Pianissimo\Component\Framework;

use ReflectionClass;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class PianoTuner
{
    public static function get(Response $response, float $startTime): string
    {
        $reflectionClass = new ReflectionClass($response->getControllerClass());
        $controllerInfo = $reflectionClass->getShortName(); //. '::' . $response->getControllerMethod();
        $originInfo = $response->getRoute() ? $response->getRoute()->getName() : $controllerInfo;

        $executionTime = round((microtime(true) - $startTime) * 1000);
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? '-';

        // Generate a hash to prevent outside manipulation/conflicts of/with the PianoTuner elements
        $hashToolbar = base_convert(md5(random_int(0,999)), 10, 36);
        $hashFunction = '_' . base_convert(md5(random_int(0,999)), 10, 36);

        $loader = new FilesystemLoader(__DIR__);
        $twig = new Environment($loader);

        return $twig->render('templates/piano_tuner.html.twig', [
            'response' => $response,
            'originInfo' => $originInfo,
            'toolbarId' => 'toolbar_' . $hashToolbar,
            'hashFunction' => $hashFunction,
            'executionTime' => $executionTime,
            'requestMethod' => $requestMethod,
        ]);
    }
}
