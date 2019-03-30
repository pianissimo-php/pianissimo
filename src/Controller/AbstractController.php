<?php

namespace App\Controller;

use App\Pianissimo\Component\Allegro\Allegro;

class AbstractController
{
    /** @var Allegro */
    private $allegroService;

    public function __construct(Allegro $allegroService)
    {
        $this->allegroService = $allegroService;
    }

    public function test(): string
    {
        return 'Dependency Injection test from extended class (AbstractController): ' . $this->allegroService->getJon();
    }
}