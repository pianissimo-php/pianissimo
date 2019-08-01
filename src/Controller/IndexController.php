<?php

namespace App\Controller;

use Pianissimo\Component\Annotation\AnnotationReader;
use Pianissimo\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Pianissimo\Component\HttpFoundation\Exception\NotFoundHttpException;
use Pianissimo\Component\HttpFoundation\JsonResponse;
use Pianissimo\Component\HttpFoundation\Response;
use Pianissimo\Component\Routing\Annotation\Route;
use Pianissimo\Component\Framework\ControllerService;
use App\TestClass;

class IndexController
{
    /**
     * @var ControllerService
     */
    private $controllerService;

    /**
     * @var AnnotationReader
     */
    private $annotationReader;

    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    public function __construct(
        ControllerService $controllerService,
        AnnotationReader $annotationReader,
        ParameterBagInterface $parameterBag
    ) {
        $this->controllerService = $controllerService;
        $this->annotationReader = $annotationReader;
        $this->parameterBag = $parameterBag;
    }

    /**
     * @Route(path="", name="app_home")
     */
    public function index(): Response
    {
        return $this->controllerService->render('index.html.twig');
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
        dd($this->annotationReader->getPropertyAnnotations(TestClass::class, 'person'));
    }

    /**
     * @Route(path="/parameter-bag", name="app_parameter_bag")
     */
    public function parameterBag(): Response
    {
        dd($this->parameterBag);
    }

    /**
     * @Route(path="/twig", name="app_twig")
     */
    public function twig(): Response
    {
        return $this->controllerService->render('twig.html.twig', [
            'title' => 'Pianissimo + Twig',
            'lead' => 'This is rendered by Twig!',
        ]);
    }

    /**
     * @Route(path="/json", name="app_json")
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
