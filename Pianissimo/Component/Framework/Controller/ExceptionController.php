<?php

namespace Pianissimo\Component\Framework\Controller;

use Pianissimo\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Pianissimo\Component\Framework\Bridge\Twig\Twig;
use Pianissimo\Component\HttpFoundation\Response;
use ReflectionClass;
use Throwable;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class ExceptionController
{
    /**
     * @var Twig
     */
    private $twig;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $loader = new FilesystemLoader(__DIR__ . DIRECTORY_SEPARATOR . '../templates/');

        $configDir = $parameterBag->get('project.dir') . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;

        $this->twig = new Environment($loader, [
            'cache' => $configDir . $parameterBag->get('cache_dir'),
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

        $response = new Response($content, $code);
        $response->setRendered(true);

        return $response;
    }
}
