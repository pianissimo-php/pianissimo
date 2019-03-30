<?php

namespace App\Controller;

use App\Pianissimo\Component\Allegro\AllegroService;

class AbstractController
{
    /** @var AllegroService */
    private $allegroService;

    public function __construct(AllegroService $allegroService)
    {
        $this->allegroService = $allegroService;
    }

    public function test(): string
    {
        return 'Dependency Injection test from extended class (AbstractController): ' . $this->allegroService->getJon();
    }
}