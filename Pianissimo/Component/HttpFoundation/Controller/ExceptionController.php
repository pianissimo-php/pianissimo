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

        $template = Path::Start(__DIR__)->back()->dir('templates')->file('exception.html.allegro')->path();

        $content = $this->allegro->render($template, [
            'exceptionName' => $exceptionName,
            'exceptionMessage' => $exception->getMessage(),
            'exceptionFile' => $exception->getFile(),
            'exceptionLine' => $exception->getLine(),
        ]);

        // TODO nice stack trace
        $tempDebug = array_map(static function ($traceItem) {
           return $traceItem['class'] . ' -> <b>' . $traceItem['function'] . '</b>';
        }, $exception->getTrace());

        $response = new Response($content . dump($tempDebug, true), $exception->getCode());
        $response->setRendered(true);

        return $response;
    }
}