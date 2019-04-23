<?php

namespace App\controllers;

class Home extends DBController
{
    protected $title = 'ALISA2';
    protected $need_access = false;

    public function index(): void
    {
        $this->twig->showTemplate('index.html', ['x' => $this->x, 'my' => $this->myUser]);
    }

    public function about(): void
    {
        $this->twig->showTemplate('about.html');
    }

}
