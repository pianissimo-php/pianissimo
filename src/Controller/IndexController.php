<?php

namespace App\Controller;

use Pianissimo\Component\Allegro\Exception\TemplateNotFoundException;
use Pianissimo\Component\Annotation\AnnotationReader;
use Pianissimo\Component\HttpFoundation\Exception\NotFoundHttpException;
use Pianissimo\Component\HttpFoundation\JsonResponse;
use Pianissimo\Component\HttpFoundation\Response;
use Pianissimo\Component\Routing\Annotation\Route;
use Pianissimo\Component\Routing\ControllerService;
use App\TestClass;

class IndexController
{
    /** @var ControllerService */
    private $controllerService;

    /** @var AnnotationReader */
    private $annotationReader;

    public function __construct(ControllerService $controllerService, AnnotationReader $annotationReader)
    {
        $this->controllerService = $controllerService;
        $this->annotationReader = $annotationReader;
    }

    /**
     * @Route(path="", name="app_home")
     */
    public function index(): Response
    {
        return $this->controllerService->render('index.html.allegro', [
            'title' => 'Pianissimo framework',
        ]);
    }

    /**
     * @Route(path="/redirect", name="app_redirect")
     * @throws NotFoundHttpException
     */
    public function redirect(): Response
    {
        return $this->controllerService->redirectToRoute('app_annotation');
    }

    /**
     * @Route(path="/annotation", name="app_annotation")
     */
    public function annotation(): Response
    {
        $annotations = $this->annotationReader->getPropertyAnnotations(TestClass::class, 'person');
        $content = dump($annotations, true);

        return $this->controllerService->render('dump.html.allegro', [
            'dump' => $content,
        ]);
    }

    /**
     * @Route(path="/allegro", name="app_allegro")
     * @throws TemplateNotFoundException
     */
    public function allegro(): Response
    {
        return $this->controllerService->render('index.html.allegro', [
            'title' => 'This is rendered by the Allegro templating engine!',
        ]);
    }

    /**
     * @Route(path="/json", name="app_annotation")
     */
    public function json(): Response
    {
        return new JsonResponse([
            'This is' => 'a json response',
            'Pianissimo' => [
                'PHP',
                'Framework',
            ],
        ]);
    }
}
