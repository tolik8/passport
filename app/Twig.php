<?php

namespace App;

use Umpirsky\Twig\Extension\PhpFunctionExtension;

class Twig
{
    protected $twig;

    public function __construct (\Twig\Environment $twig)
    {
        $this->twig = $twig;
        $functions = [
            'filetime',
        ];
        $this->twig->addExtension(new PhpFunctionExtension($functions));
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