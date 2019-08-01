<?php

namespace Pianissimo\Component\Framework;

use Pianissimo\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Pianissimo\Component\HttpFoundation\Exception\NotFoundHttpException;
use Pianissimo\Component\HttpFoundation\RedirectResponse;
use Pianissimo\Component\HttpFoundation\Response;
use Pianissimo\Component\Routing\Exception\RouteNotFoundException;
use Pianissimo\Component\Routing\RouterInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class ControllerService
{
    /**
     * @var RouterInterface
     */
    private $routingService;

    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    public function __construct(
        RouterInterface $routingService,
        ParameterBagInterface $parameterBag
    ) {
        $this->routingService = $routingService;
        $this->parameterBag = $parameterBag;
    }

    /**
     * @throws NotFoundHttpException
     */
    public function redirectToRoute(string $routeName): RedirectResponse
    {
        try {
            $route = $this->routingService->getRoute($routeName);
        } catch (RouteNotFoundException $e) {
            throw new NotFoundHttpException('404 Not found');
        }

        return new RedirectResponse($route->getPath());
    }

    public function render(string $template, array $data = []): Response
    {
        $configDir = $this->parameterBag->get('project.dir') . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;

        $loader = new FilesystemLoader($configDir . $this->parameterBag->get('templates_dir'));

        $twig = new Environment($loader, [
            'cache' => $configDir . $this->parameterBag->get('cache_dir'),
        ]);

        $response = new Response($twig->render($template, $data));
        $response->setRendered(true);

        return $response;
    }
}
