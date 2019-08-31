<?php

namespace Pianissimo\Component\Framework\Controller;

use Pianissimo\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Pianissimo\Component\Framework\Bridge\Twig\Twig;
use ReflectionClass;
use Pianissimo\Component\Framework\Response;
use Throwable;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class ExceptionController
{
    /**
     * @var Twig
     */
    private $twig;

    public function __construct()
    {
        $loader = new FilesystemLoader(__DIR__ . DIRECTORY_SEPARATOR . '../templates/');

        $this->twig = new Environment($loader, [
            'cache' => false,
        ]);
    }

    public function index(Throwable $exception): Response
    {
        $exceptionName = (new ReflectionClass($exception))->getShortName();

        $content = $this->twig->render('exception.html.twig', [
            'exceptionName' => $exceptionName,
            'exceptionMessage' => $exception->getMessage(),
            'exceptionFile' => $exception->getFile(),
            'exceptionLine' => $exception->getLine(),
        ]);

        $code = $exception->getCode();

        if ($code === 0) {
            $code = 500;
        }

        return new Response($content, $code);
    }
}
