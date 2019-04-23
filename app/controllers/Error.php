<?php

namespace App\controllers;

class Error extends Controller
{
    public function e404(): void
    {
        $this->twig->showTemplate('404.html');
    }

    public function e405(): void
    {
        $this->twig->showTemplate('405.html');
    }

}
