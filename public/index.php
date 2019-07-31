<?php

use App\Core;
use Pianissimo\Component\HttpFoundation\Request;

require __DIR__ . '/../vendor/autoload.php';

$core = new Core('dev', true);

$request = Request::fromGlobals();
$response = $core->handle($request);
$core->send($response);
