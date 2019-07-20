<?php

namespace Pianissimo\Component\Framework;

use Pianissimo\Component\Allegro\Allegro;
use Pianissimo\Component\Allegro\Exception\TemplateNotFoundException;
use Pianissimo\Component\HttpFoundation\RedirectResponse;
use Pianissimo\Component\HttpFoundation\Response;
use Pianissimo\Component\Routing\Exception\RouteNotFoundException;
use Pianissimo\Component\Routing\RoutingService;

class ControllerService
{
    /** @var RoutingService */
    private $routingService;

    /** @var Allegro */
    private $allegro;

    public function __construct(RoutingService $routingService, Allegro $allegro)
    {
        $this->routingService = $routingService;
        $this->allegro = $allegro;
    }

    /**
     * @throws RouteNotFoundException
     */
    public function redirectToRoute(string $routeName): RedirectResponse
    {
        $route = $this->routingService->getRoute($routeName);
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
