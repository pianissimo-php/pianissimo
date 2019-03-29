<?php

namespace App\Pianissimo;

use App\Pianissimo\Component\Annotation\AnnotationReader;
use App\Pianissimo\Component\Routing\RoutingService;
use App\TestClass;

class PianoEngine
{
    /** @var RoutingService */
    private $routingService;

    /** @var AnnotationReader */
    private $annotationReader;

    public function __construct(RoutingService $routingService, AnnotationReader $annotationReader)
    {
        $this->routingService = $routingService;
        $this->annotationReader = $annotationReader;
    }

    /**
     * Each request wil go through the PianoEngine. From this place, all services will be auto wired!
     */
    public function start(): void
    {
        $this->routingService->initializeRoutes();

        $path = $_SERVER['PATH_INFO'] ?? '';
        $route = $this->routingService->matchRoute($path);
        dump($route);

        $annotations = $this->annotationReader->getPropertyAnnotations(TestClass::class, 'person');
        dump($annotations);

        die;
        /*
        if (isset($_SERVER['QUERY_STRING'])) {
            dump($_SERVER['QUERY_STRING']);
        }
        */
    }
}