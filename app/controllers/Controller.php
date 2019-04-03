<?php

namespace App\controllers;

use App\Twig;
use App\Breadcrumb;

class Controller
{
    protected $root;
    protected $twig;
    protected $bc;
    protected $x;
    protected $new_guid;
    protected $c_distr;
    protected $role;
    protected $need_access = true;
    protected $title;

    public function __construct (Twig $twig, Breadcrumb $bc)
    {
        $this->root = $_SERVER['DOCUMENT_ROOT'];
        $this->twig = $twig;
        $this->bc = $bc;

        $this->x['title'] = $this->title;
    }
}