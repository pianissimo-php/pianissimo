<?php

namespace Pianissimo\Component\HttpFoundation\Controller;

use Pianissimo\Component\Allegro\Allegro;
use Pianissimo\Component\Finder\Path;
use Pianissimo\Component\HttpFoundation\Response;
use ReflectionClass;
use Throwable;

class ExceptionController
{
    /** @var Allegro */
    private $allegro;

    public function __construct(Allegro $allegro)
    {
        $this->allegro = $allegro;
    }

    public function index(Throwable $exception): Response
    {
        $exceptionName = (new ReflectionClass($exception))->getShortName();

        /*
        $trace = debug_backtrace();
        foreach ($trace as $traceItem) {
            dump($traceItem['class'] . '::' . $traceItem['function']);
        }
        */

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