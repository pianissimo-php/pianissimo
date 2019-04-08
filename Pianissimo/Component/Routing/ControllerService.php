<?php

namespace Pianissimo\Component\Routing;

use Pianissimo\Component\Allegro\Allegro;
use Pianissimo\Component\Allegro\Exception\TemplateNotFoundException;
use Pianissimo\Component\HttpFoundation\Exception\NotFoundHttpException;
use Pianissimo\Component\HttpFoundation\RedirectResponse;
use Pianissimo\Component\HttpFoundation\Response;

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
     * @throws NotFoundHttpException
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