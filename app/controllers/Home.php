<?php

namespace App\controllers;

class Home extends DBController
{
    protected $title = 'Passport';
    protected $need_access = false;

    public function index(): void
    {
        $this->twig->showTemplate('index.html', ['x' => $this->x, 'my' => $this->myUser]);
    }

}
