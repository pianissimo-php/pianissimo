<?php

namespace Pianissimo\Component\Framework;

use InvalidArgumentException;
use Pianissimo\Component\Framework\Bridge\Twig\Twig;
use Pianissimo\Component\Framework\Exception\NotFoundHttpException;
use Pianissimo\Component\Routing\Exception\RouteNotFoundException;
use Pianissimo\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ControllerService
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var Twig
     */
    private $twig;

    public function __construct(
        RouterInterface $router,
        Twig $twig
    ) {
        $this->router = $router;
        $this->twig = $twig;
    }

    /**
     * @throws NotFoundHttpException
     */
    public function redirectToRoute(string $routeName): RedirectResponse
    {
        try {
            $route = $this->router->getRoute($routeName);
        } catch (RouteNotFoundException $e) {
            throw new NotFoundHttpException('404 Not found');
        }

        return new RedirectResponse($route->getPath());
    }

    /**
     * @throws RouteNotFoundException
     * @throws InvalidArgumentException
     */
    public function generateUrl(string $routeName, array $parameters): string
    {
        return $this->router->generateUrl($routeName, $parameters);
    }

    public function render(string $template, array $data = []): Response
    {
        return new Response($this->twig->render($template, $data));
    }
}
