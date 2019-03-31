<?php

namespace App\Controller;

use App\Pianissimo\Component\Allegro\Allegro;
use App\Pianissimo\Component\Annotation\AnnotationReader;
use App\Pianissimo\Component\HttpFoundation\Response;
use App\Pianissimo\Component\Routing\Annotation\Route;
use App\TestClass;

class IndexController
{
    /** @var AnnotationReader */
    private $annotationReader;

    /** @var Allegro */
    private $allegro;

    public function __construct(AnnotationReader $annotationReader, Allegro $allegro)
    {
        $this->annotationReader = $annotationReader;
        $this->allegro = $allegro;
    }

    /**
     * @Route(path="", name="app_home")
     * @Route(path="/jon", name="app_jon")
     */
    public function index(): Response
    {
        $annotations = $this->annotationReader->getPropertyAnnotations(TestClass::class, 'person');

        $content = dump($annotations, true);
        return new Response($content);
    }

    /**
     * @Route(path="/allegro", name="app_develop")
     */
    public function develop(): Response
    {
        return new Response($this->allegro->render('index.html.allegro'));
    }
}