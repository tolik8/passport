<?php

namespace App\controllers;

use App\Helper;

class Adminka extends DBController
{
    protected $role = '7'; // Ğîëü 7 - Àäì³íêà
    protected $title = 'Àäì³íêà';

    public function index(): void
    {
        $this->x['menu'] = $this->bc->getMenu('adminka');
        $this->twig->showTemplate('adminka/index.html', ['x' => $this->x, 'my' => $this->myUser]);
    }

    public function passport(): void
    {
        $this->x['menu'] = $this->bc->getMenu('passport');
        $this->twig->showTemplate('adminka/passport.html', ['x' => $this->x, 'my' => $this->myUser]);
    }

    public function user_find(): void
    {
        //$find = $_POST['query'];
        $find = filter_input(INPUT_POST, 'query', FILTER_SANITIZE_SPECIAL_CHARS);
        $sql = getSQL('adminka/user_find.sql');
        $res = $this->db->selectRaw($sql, ['find' => Helper::cp1251($find)])->get();
        $users = ['suggestions' => $res];
        /** @noinspection PhpComposerExtensionStubsInspection */
        echo json_encode(Helper::arrayToUtf8($users));
    }

    public function users(): void
    {
        $this->x['menu'] = $this->bc->getMenu('users');

        $this->x['find'] = $find = filter_input(INPUT_POST, 'find', FILTER_SANITIZE_SPECIAL_CHARS);
        $sql = getSQL('adminka/user_find.sql');
        $this->x['users'] = $this->db->selectRaw($sql, ['find' => $find])->get();

        $this->twig->showTemplate('adminka/users.html', ['x' => $this->x, 'my' => $this->myUser]);
    }

    public function user($guid): void
    {
        $this->x['menu'] = $this->bc->getMenu('user');
        $this->x['guid'] = $guid;
        $sql = getSQL('adminka/get_user.sql');
        $this->x['user'] = $this->db->selectRaw($sql, ['guid' => $guid])->first();
        $sql = getSQL('adminka/get_passport_access.sql');
        $this->x['tasks'] = $this->db->selectRaw($sql, ['guid' => $guid])->get();
        if (isset($_SESSION['update']) && $_SESSION['update']) {
            $this->x['update'] = true;
            unset($_SESSION['update']);
        }

        $this->twig->showTemplate('adminka/user.html', ['x' => $this->x, 'my' => $this->myUser]);
    }

    public function passport_access_update(): void
    {
        $guid = Helper::checkRegEx('guid', $_POST['guid'], '');
        $tasks_id = Helper::getArrayIdFromPost($_POST, 'id');

        $this->db->beginTransaction();

        $this->db->table('PIKALKA.pass_access')->where('guid = :guid')
            ->bind(['guid' => $guid])->delete();

        if (count($tasks_id) > 0) {
            $sql = 'INSERT ALL' . PHP_EOL;
            foreach ($tasks_id as $item) {
                $sql .= 'INTO PIKALKA.pass_access (guid, task_id) VALUES (\'' . $guid . '\', ' . $item . ')' . PHP_EOL;
            }
            $sql .= 'SELECT * FROM dual';
            $res = $this->db->statement($sql);
            if ($res === '00000') {$_SESSION['update'] = true;}
        } else {
            $_SESSION['update'] = true;
        }
        $this->db->endTransaction();

        header('Location: /adminka/passport/user/' . $guid);
    }

}
