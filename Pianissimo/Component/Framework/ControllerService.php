<?php

namespace Pianissimo\Component\Framework;

use Pianissimo\Component\Framework\Bridge\Twig;
use Pianissimo\Component\HttpFoundation\Exception\NotFoundHttpException;
use Pianissimo\Component\HttpFoundation\RedirectResponse;
use Pianissimo\Component\HttpFoundation\Response;
use Pianissimo\Component\Routing\Exception\RouteNotFoundException;
use Pianissimo\Component\Routing\RouterInterface;

class ControllerService
{
    /**
     * @var RouterInterface
     */
    private $routingService;

    /**
     * @var Twig
     */
    private $twig;

    public function __construct(
        RouterInterface $routingService,
        Twig $twig
    ) {
        $this->routingService = $routingService;
        $this->twig = $twig;
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
        $response = new Response($this->twig->render($template, $data));
        $response->setRendered(true);

        return $response;
    }
}
