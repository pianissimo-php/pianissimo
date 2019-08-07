<?php

namespace Pianissimo\Component\HttpFoundation\Controller;

use Pianissimo\Component\Finder\Path;
use Pianissimo\Component\HttpFoundation\Response;
use ReflectionClass;
use Throwable;

class ExceptionController
{
    public function index(Throwable $exception): Response
    {
        $exceptionName = (new ReflectionClass($exception))->getShortName();

        $template = Path::Start(__DIR__)->back()->dir('templates')->file('exception.html.allegro')->path();

        $content = $this->allegro->render($template, [
            'exceptionName' => $exceptionName,
            'exceptionMessage' => $exception->getMessage(),
            'exceptionFile' => $exception->getFile(),
            'exceptionLine' => $exception->getLine(),
        ]);

        $response = new Response($content, $exception->getCode());
        $response->setRendered(true);

        return $response;
    }
}
