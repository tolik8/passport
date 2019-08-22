<?php

namespace App\controllers;

use App\Twig;
use App\Breadcrumb;
use App\QueryBuilder;
use App\MyUser;
use App\Tax;
use App\Helper;

class DBController extends Controller
{
    protected $db;
    protected $myUser;
    protected $tax;

    public function __construct(Twig $twig, Breadcrumb $bc, QueryBuilder $db, MyUser $myUser, Tax $tax)
    {
        parent::__construct($twig, $bc);
        $this->db = $db;
        $this->myUser = $myUser;
        $this->tax = $tax;

        // якщо сторінка вимагає доступ і цього доступа немає то перекинути на головну
        $access = Helper::in_string($this->myUser->roles, $this->role);
        if (!$access && $this->need_access) {header('Location: 10.19.19.121'); Exit;}

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
