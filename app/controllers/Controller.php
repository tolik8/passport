<?php

namespace App\controllers;

use App\QueryBuilder;
use App\Twig;
use App\MyUser;
use App\Breadcrumb;

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
    protected $ss;
    protected $role;
    protected $need_access = true;
    protected $title;

    public function __construct (Twig $twig, QueryBuilder $db, MyUser $myUser, Breadcrumb $bc)
    {
        $this->root = $_SERVER['DOCUMENT_ROOT'];
        $this->twig = $twig;
        $this->db = $db;
        $this->myUser = $myUser;
        $this->bc = $bc;
        $access = in_string($this->role, $this->myUser->roles);
        $this->x['title'] = $this->title;

        if (!$access && $this->need_access) {
            $this->twig->showTemplate('index.html', ['my' => $this->myUser]);
            if (DEBUG) {d($this);}
            Exit;
        }

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