<?php

namespace Pianissimo\Component\Framework;

use Pianissimo\Component\Allegro\Allegro;
use Pianissimo\Component\Allegro\Exception\TemplateNotFoundException;
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
     * @var Allegro
     */
    private $allegro;

    public function __construct(RouterInterface $routingService, Allegro $allegro)
    {
        $this->routingService = $routingService;
        $this->allegro = $allegro;
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

    /**
     * @throws TemplateNotFoundException
     */
    public function render(string $template, array $data = []): Response
    {
        $response = new Response($this->allegro->render($template, $data));
        $response->setRendered(true);

        return $response;
    }
}
