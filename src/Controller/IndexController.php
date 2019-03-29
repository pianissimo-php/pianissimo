<?php

namespace App\Controller;

use App\Pianissimo\Component\Annotation\AnnotationReader;
use App\Pianissimo\Component\HttpFoundation\Response;
use App\Pianissimo\Component\Routing\Annotation\Route;
use App\TestClass;

class IndexController
{
    /** @var AnnotationReader */
    private $annotationReader;

    public function __construct(AnnotationReader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    /**
     * @Route(path="/jon/mulder", name="app_home")
     * @Route(path="/jon/mulder/2", name="app_home2")
     */
    public function index(): Response
    {
        $annotations = $this->annotationReader->getPropertyAnnotations(TestClass::class, 'person');
        dump($annotations);

        /*
        if (isset($_SERVER['QUERY_STRING'])) {
            dump($_SERVER['QUERY_STRING']);
        }
        */

        return new Response();
    }
}