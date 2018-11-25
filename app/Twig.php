<?php

namespace App;

class Twig
{
    protected $twig;

    public function __construct (\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    public function showTemplate ($template, $params = [])
    {
        try {
            echo $this->twig->render($template, $params);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}