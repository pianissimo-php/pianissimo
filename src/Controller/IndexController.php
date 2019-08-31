<?php

namespace App\Controller;

use App\Dummy\SophieLetitiaRoxane;
use Pianissimo\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Pianissimo\Component\Routing\Annotation\Route;
use Pianissimo\Component\Framework\ControllerService;
use Pianissimo\Component\Framework\Response;

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
        ParameterBagInterface $parameterBag,
        SophieLetitiaRoxane $sophie
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

    public function parameterBag(): Response
    {
        dd($this->parameterBag);
    }

    public function personTest(string $name): Response
    {
        dd($name);
    }
}
