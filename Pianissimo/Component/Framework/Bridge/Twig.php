<?php

namespace Pianissimo\Component\Framework\Bridge;

use Pianissimo\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Environment;
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
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function render($name, array $context = []): string
    {
        return $this->environment->render($name, $context);
    }
}
