<?php

namespace App\controllers;

class Cookie 
{
    protected $db;

    public function __construct (\App\QueryBuilder $db)
    {
        $this->db = $db;
    }

    public function index ($cookie): void
    {
        $row = $this->db->getOneRow('PIKALKA.people', ['cookie' => $cookie]);
        if (count($row) > 0) {
            $my['guid'] = $row['GUID'];
            $my['login'] = $row['LOGIN'];
            $my['fio1'] = $row['FIO1'];
            $my['fio2'] = $row['FIO2'];
            $my['fio3'] = $row['FIO3'];
            $my['viddil'] = $row['VIDDIL_ID'];

            $sql = file_get_contents('../sql/myuser/get_roles.sql');
            $my_roles = $this->db->getOneColFromSQL($sql, ['guid' => $my['guid']]);
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

    public function noCookie (): void
    {
        $server = ['alisa2.loc' => 'alisa.loc', 'start2.tr.sta' => 'start.tr.sta', '10.19.19.122' => '10.19.191.121'];
        header('Location: http://' . $server[$_SERVER['SERVER_NAME']] . '/login.php');
        exit;
    }
}