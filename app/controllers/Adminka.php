<?php

namespace App\controllers;
use App\Helper;

class Adminka extends Controller
{
    protected $role = '7'; // Ðîëü 7 - Àäì³íêà
    protected $title = 'Àäì³íêà';

    public function index (): void
    {
        $this->x['menu'] = $this->bc->getMenu('adminka');
        $this->twig->showTemplate('adminka/index.html', ['x' => $this->x, 'my' => $this->myUser]);
    }

    public function passport (): void
    {
        $this->x['menu'] = $this->bc->getMenu('passport');
        $this->twig->showTemplate('adminka/passport.html', ['x' => $this->x, 'my' => $this->myUser]);
    }

    public function user_find (): void
    {
        //$find = $_POST['query'];
        $find = filter_input(INPUT_POST, 'query', FILTER_SANITIZE_SPECIAL_CHARS);
        $sql = getSQL('adminka/user_find.sql');
        $res = $this->db->getAllFromSQL($sql, ['find' => Helper::cp1251($find)]);
        $users = ['suggestions' => $res];
        /** @noinspection PhpComposerExtensionStubsInspection */
        echo json_encode(Helper::ArrayToUtf8($users));
    }

    public function users (): void
    {
        $this->x['menu'] = $this->bc->getMenu('users');

        $this->x['find'] = $find = filter_input(INPUT_POST, 'find', FILTER_SANITIZE_SPECIAL_CHARS);
        $sql = getSQL('adminka/user_find.sql');
        $this->x['users'] = $this->db->getAllFromSQL($sql, ['find' => $find]);

        $this->twig->showTemplate('adminka/users.html', ['x' => $this->x, 'my' => $this->myUser]);
    }

    public function user ($guid): void
    {
        $this->x['menu'] = $this->bc->getMenu('user');
        $this->x['guid'] = $guid;
        $sql = getSQL('adminka/get_user.sql');
        $this->x['user'] = $this->db->getOneRowFromSQL($sql, ['guid' => $guid]);
        $sql = getSQL('adminka/get_passport_access.sql');
        $this->x['works'] = $this->db->getAllFromSQL($sql, ['guid' => $guid]);
        if (isset($_SESSION['update']) && $_SESSION['update']) {
            $this->x['update'] = true;
            unset($_SESSION['update']);
        }

        $this->twig->showTemplate('adminka/user.html', ['x' => $this->x, 'my' => $this->myUser]);
    }

    public function passport_access_update (): void
    {
        $pattern = '#^[0-9a-zA-Z]{32}$#';
        $guid = Helper::regex($pattern, $_POST['guid'], 0);
        $works_id = Helper::getArrayIdFromPost($_POST, 'id');

        $this->db->delete('PIKALKA.pass_access', ['guid' => $guid]);

        if (count($works_id) > 0) {
            $sql = 'INSERT ALL' . CR;
            foreach ($works_id as $item) {
                $sql .= 'INTO PIKALKA.pass_access (guid, work_id) VALUES (\'' . $guid . '\', ' . $item . ')' . CR;
            }
            $sql .= 'SELECT * FROM dual';
            $res = $this->db->runSQL($sql);
            if ($res > 0) {$_SESSION['update'] = true;}
        } else {
            $_SESSION['update'] = true;
        }

        header('Location: /adminka/passport/user/' . $guid);
    }
}