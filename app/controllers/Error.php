<?php

namespace App\controllers;

class Error 
{
    protected $twig;

    public function __construct (\App\Twig $twig)
    {
        $this->twig = $twig;
    }

    public function e404 (): void
    {
        $this->twig->showTemplate('404.html');
    }

    public function e405 (): void
    {
        $this->twig->showTemplate('405.html');
    }

}