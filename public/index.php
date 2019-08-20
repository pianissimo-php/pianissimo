<?php

use App\Core;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Pianissimo\Component\HttpFoundation\Request;

require __DIR__ . '/../vendor/autoload.php';

AnnotationRegistry::registerLoader(function (string $class) {
    return \class_exists($class);
});

$core = new Core('dev', true);

$request = Request::fromGlobals();
$response = $core->handle($request);
$core->send($response);
