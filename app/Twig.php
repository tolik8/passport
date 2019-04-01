<?php

namespace App;

class Twig
{
    protected $twig;

    public function __construct (\Twig\Environment $twig)
    {
        $this->twig = $twig;
    }

    public function showTemplate ($template, $params = []): void
    {
        try {
            echo $this->twig->render($template, $params);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}