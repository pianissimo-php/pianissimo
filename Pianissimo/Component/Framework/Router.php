<?php

namespace Pianissimo\Component\Framework;

use Pianissimo\Component\Framework\Routing\AnnotatedRouteLoader;
use Pianissimo\Component\Routing\Router as BaseRouter;

class Router extends BaseRouter
{
    public function __construct(AnnotatedRouteLoader $annotatedRouteLoader)
    {
        parent::__construct();

        $this->routeCollection = $annotatedRouteLoader->load();
    }
}
