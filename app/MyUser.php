<?php
/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */

namespace App;

class MyUser
{
    protected $db;
    public $guid;
    public $login;
    public $fio1;
    public $fio2;
    public $fio3;
    public $viddil;
    public $roles;
    public $admin = false;

    public function __construct (\App\QueryBuilder $db)
    {
        $this->db = $db;

        if (!isset($_SESSION['my']['guid'])) {
            $_SESSION['token_uri'] = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
            $server = ['alisa2.loc' => 'alisa.loc', 'start2.tr.sta' => 'start.tr.sta', '10.19.19.122' => '10.19.191.121'];
            header('Location: http://' . $server[$_SERVER['SERVER_NAME']] . '/token.php');
        } else {
            $this->guid = $_SESSION['my']['guid'];
            $this->login = $_SESSION['my']['login'];
            $this->fio1 = $_SESSION['my']['fio1'];
            $this->fio2 = $_SESSION['my']['fio2'];
            $this->fio3 = $_SESSION['my']['fio3'];
            $this->viddil = $_SESSION['my']['viddil'];
            $this->roles = $_SESSION['my']['roles'];
            $this->admin = $_SESSION['my']['admin'];
            
            if ($this->admin) {
                ini_set('error_reporting', E_ALL);
                ini_set('display_errors', 1);
                ini_set('display_startup_errors', 1);
            }
            
            /*$row = $this->db->getOneRow('PIKALKA.people', ['guid' => $_SESSION['my']['guid'] ]);
            if (count($row) > 0) {
                $this->login = $row['LOGIN'];
                $this->fio1 = $row['FIO1'];
                $this->fio2 = $row['FIO2'];
                $this->fio3 = $row['FIO3'];
                $this->viddil = $row['VIDDIL_ID'];
                $sql = file_get_contents('../sql/myuser/get_roles.sql');
                $my_roles = $this->db->getOneColFromSQL($sql, ['guid' => $this->guid]);
                $this->roles = implode(',', $my_roles);
            }*/
        }
    }

    public function refresh (): void
    {
        session_destroy();
        header('Location: /');
    }

    public function logout (): void
    {
        session_destroy();
        setcookie('alisa2', '', time() );
        header('Location: /');
    }
}