<?php

namespace App\Controller;

use App\Pianissimo\Component\Routing\Annotation\Route;

class IndexController
{
    /**
     * @Route(path="/jon/mulder", name="app_home")
     * @Route(path="/jon/mulder/2", name="app_home2")
     */
    public function index()
    {
        return '123123';
    }
}