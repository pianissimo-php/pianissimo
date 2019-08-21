<?php

namespace Pianissimo\Component\Framework;

use Pianissimo\Component\Framework\Routing\AnnotatedRouteLoader;
use Pianissimo\Component\Framework\Routing\YamlRouteLoader;
use Pianissimo\Component\Routing\Router as BaseRouter;

class Router extends BaseRouter
{
    public function __construct(AnnotatedRouteLoader $annotatedRouteLoader, YamlRouteLoader $yamlRouteLoader)
    {
        $this
            ->addLoader($annotatedRouteLoader)
            ->addLoader($yamlRouteLoader);
    }
}
