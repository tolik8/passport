<?php

namespace App\controllers;

use App\QueryBuilder;
use App\Twig;
use App\MyUser;
use App\Breadcrumb;
use App\Helper;
use App\Tax;

class Controller
{
    protected $root;
    protected $twig;
    protected $db;
    protected $myUser;
    protected $bc;
    protected $x;
    protected $new_guid;
    protected $c_distr;
    protected $role;
    protected $need_access = true;
    protected $title;
    protected $tax;

    public function __construct (Twig $twig, QueryBuilder $db, MyUser $myUser, Breadcrumb $bc, Tax $tax)
    {
        $this->root = $_SERVER['DOCUMENT_ROOT'];
        $this->twig = $twig;
        $this->db = $db;
        $this->myUser = $myUser;
        $this->bc = $bc;
        $this->tax = $tax;

        $access = Helper::in_string($this->myUser->roles, $this->role);
        $this->x['title'] = $this->title;

        // якщо сторінка вимагає доступ і цього доступа немає то перекинути на головну
        if (!$access && $this->need_access) {header('Location: /'); Exit;}

        if ($this->bc->isUnderConstruct) {
            try {
                $this->x['img_number'] = random_int(0, 9);
            } catch (\Exception $e) {
                $this->x['img_number'] = 0;
            }
            $this->twig->showTemplate('isUnderConstruct.html', ['x' => $this->x, 'my' => $this->myUser]);
            Exit;
        }
    }
}