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
        $configDir = $parameterBag->get('project_dir') . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;

        $loader = new FilesystemLoader($configDir . $parameterBag->get('templates_dir'));

        // Use cache only when not in a dev environment.
        $cache = $parameterBag->get('environment') !== 'dev' ? $configDir . $parameterBag->get('cache_dir') : false;

        $this->environment = new Environment($loader, [
            'cache' => $cache,
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
