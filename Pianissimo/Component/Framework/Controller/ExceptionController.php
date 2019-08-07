<?php

namespace Pianissimo\Component\Framework\Controller;

use Pianissimo\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Pianissimo\Component\HttpFoundation\Response;
use ReflectionClass;
use Throwable;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class ExceptionController
{
    /**
     * @var Environment
     */
    private $twig;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $configDir = $parameterBag->get('project.dir') . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;

        $loader = new FilesystemLoader('../Pianissimo/Component/Framework/templates/');

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

        $response = new Response($content, $exception->getCode());
        $response->setRendered(true);

        return $response;
    }
}
