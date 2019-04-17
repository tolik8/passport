<?php

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
    public $debug;
    public $log_files_count = 0;
    public $table_error_count = 0;

    public function __construct (QueryBuilder $db)
    {
        $this->db = $db;

        if (!isset($_SESSION['my']['guid'])) {
            $_SESSION['cookie_uri'] = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
            $server = ['alisa2.loc' => 'alisa.loc', 'start2.tr.sta' => 'start.tr.sta', '10.19.19.122' => '10.19.191.121'];
            header('Location: http://' . $server[$_SERVER['SERVER_NAME']] . '/cookie.php');
            exit;
        }

        $this->guid = $_SESSION['my']['guid'];
        $this->login = $_SESSION['my']['login'];
        $this->fio1 = $_SESSION['my']['fio1'];
        $this->fio2 = $_SESSION['my']['fio2'];
        $this->fio3 = $_SESSION['my']['fio3'];
        $this->viddil = $_SESSION['my']['viddil'];
        $this->roles = $_SESSION['my']['roles'];
        $this->admin = $_SESSION['my']['admin'];
        $this->debug = DEBUG;

        if ($_SERVER['REMOTE_ADDR'] === '10.19.190.164' || $_SERVER['REMOTE_ADDR'] === '127.0.0.1' || $this->login === 'admin19t') {
            $this->debug = true;
        }

        if ($this->admin) {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            ini_set('error_reporting', E_ALL);
            $this->log_files_count = count(scandir('../logs/err', SCANDIR_SORT_NONE)) - 2;
            $this->table_error_count = $db->table('PIKALKA.pass_errors')
                ->select('COUNT(*)')->getCell();
        }

    }

    public function refresh (): void
    {
        session_destroy();
        header('Location: /');
        exit;
    }

    public function logout (): void
    {
        session_destroy();
        setcookie('alisa2', '', time() );
        header('Location: /');
        exit;
    }
}