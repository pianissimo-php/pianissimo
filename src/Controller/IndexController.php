<?php

namespace App\Controller;

use Pianissimo\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Pianissimo\Component\HttpFoundation\Exception\NotFoundHttpException;
use Pianissimo\Component\HttpFoundation\JsonResponse;
use Pianissimo\Component\HttpFoundation\Response;
use Pianissimo\Component\Routing\Annotation\Route;
use Pianissimo\Component\Framework\ControllerService;

class IndexController
{
    /**
     * @var ControllerService
     */
    private $controllerService;

    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    public function __construct(
        ControllerService $controllerService,
        ParameterBagInterface $parameterBag
    ) {
        $this->controllerService = $controllerService;
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
     * @Route(path="/person/{id}/{name}", name="app_person")
     */
    public function person(int $id, string $name): Response
    {
        $routeUrl = $this->controllerService->generateUrl('app_person', [
            'id' => 8,
            'name' => 'john_doe',
        ]);

        return $this->controllerService->render('person.html.twig', [
            'id' => $id,
            'name' => $name,
            'routeUrl' => $routeUrl,
        ]);
    }

    /**
     * @Route(path="/redirect", name="app_redirect")
     * @throws NotFoundHttpException
     */
    public function redirect(): Response
    {
        return $this->controllerService->redirectToRoute('app_twig');
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
