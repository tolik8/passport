<?php

namespace App\controllers;

use App\QueryBuilder;

class Cookie 
{
    protected $db;

    public function __construct(QueryBuilder $db)
    {
        $this->db = $db;
    }

    public function index($cookie): void
    {
        $row = $this->db->table('PIKALKA.people')->where('cookie = :cookie')
            ->bind(['cookie' => $cookie])->first();
        if (count($row) > 0) {
            $my['guid'] = $row['GUID'];
            $my['login'] = $row['LOGIN'];
            $my['fio1'] = $row['FIO1'];
            $my['fio2'] = $row['FIO2'];
            $my['fio3'] = $row['FIO3'];
            $my['viddil'] = $row['VIDDIL_ID'];

            $sql = getSQL('myuser/get_roles.sql');
            $my_roles = $this->db->selectRaw($sql, ['guid' => $my['guid']])->pluck();
            $my['roles'] = implode(',', $my_roles);

            if (in_array('7', $my_roles, true)) {$my['admin'] = true;} else {$my['admin'] = false;}

            $_SESSION['my'] = $my;

            if (isset($_SESSION['cookie_uri'])) {
                $cookie_uri = $_SESSION['cookie_uri'];
                unset($_SESSION['cookie_uri']);
                header('Location: ' . $cookie_uri);
            } else {
                header('Location: /');
            }
            exit;
        }
    }

    public function noCookie(): void
    {
        $server = [
            'passport.loc' => 'alisa.loc',
            'start2.tr.sta' => 'start.tr.sta',
            '10.19.19.122:88' => '10.19.19.121'
        ];
        header('Location: http://' . $server[$_SERVER['HTTP_HOST']] . '/login.php');
        exit;
    }

}
