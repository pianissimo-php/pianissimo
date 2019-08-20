<?php

namespace Pianissimo\Component\Framework\Bridge\Twig;

use Pianissimo\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;

class Twig
{
    /**
     * @var Environment
     */
    private $environment;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $configDir = $parameterBag->get('project.dir') . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;

        $loader = new FilesystemLoader($configDir . $parameterBag->get('templates_dir'));

        $this->environment = new Environment($loader, [
            'cache' => $configDir . $parameterBag->get('cache_dir'),
        ]);
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function render($name, array $context = []): string
    {
        return $this->environment->render($name, $context);
    }
}
