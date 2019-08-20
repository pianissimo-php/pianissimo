<?php

namespace Pianissimo\Component\Framework;

use LogicException;
use Pianissimo\Component\DependencyInjection\ContainerInterface;
use Pianissimo\Component\Framework\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;

class ControllerResolver
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Router
     */
    private $router;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->router = $container->get(Router::class);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function resolve(Request $request): Callable
    {
        $router = $this->router;

        $path = $_SERVER['PATH_INFO'] ?? '';
        $route = $router->matchRoute($path);

        if ($route === null) {
            throw new NotFoundHttpException('404 Not Found');
        }

        $class = $route->getClass();
        $method = $route->getMethod();
        $parameters = $this->resolveParameters($route->getPath(), $path);

        $controller = $this->container->get($class);

        return function () use ($controller, $method, $parameters) {
            return $controller->$method(...$parameters);
        };
    }

    /**
     * Resolves the route parameters using the requested path.
     */
    private function resolveParameters(string $routePath, string $requestPath): array
    {
        $routeParts = array_values(array_filter(explode('/', $routePath)));
        $requestPathParts = array_values(array_filter(explode('/', $requestPath)));

        $parameters = [];
        $count = -1;

        foreach ($routeParts as $routePart) {
            $count++;

            if (array_key_exists($count, $requestPathParts) === false) {
                throw new LogicException('Can not resolve route parameter, route does not match with the requested URL');
            }

            if ($routePart === $requestPathParts[$count]) {
                continue;
            }

            $firstCharacter = substr($routePart, 0, 1);
            $lastCharacter = substr($routePart, -1);

            if ($firstCharacter === '{' && $lastCharacter === '}') {
                $parameters[] = $requestPathParts[$count];

                continue;
            }

            throw new LogicException('Can not resolve route parameter, route does not match with the requested URL');
        }

        return $parameters;
    }
}
