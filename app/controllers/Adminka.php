<?php

namespace App\controllers;

class Adminka extends Controller
{
    protected $role = '7'; // Ðîëü 7 - Àäì³íêà
    protected $title = 'Àäì³íêà';

    public function index (): void
    {
        $this->twig->showTemplate('adminka/index.html', ['x' => $this->x, 'my' => $this->myUser]);
    }

    public function passport (): void
    {
        $this->twig->showTemplate('adminka/passport.html', ['x' => $this->x, 'my' => $this->myUser]);
    }

    public function user_find (): void
    {
        //$find = $_POST['query'];
        $find = filter_input(INPUT_POST, 'query', FILTER_SANITIZE_SPECIAL_CHARS);
        $sql = file_get_contents($this->root . '/sql/user_find.sql');
        $res = $this->db->getAllFromSQL($sql, ['find' => cp1251($find)]);
        $users = ['suggestions' => $res];
        /** @noinspection PhpComposerExtensionStubsInspection */
        echo json_encode(ArrayToUtf8($users));
    }

    public function users (): void
    {
        $this->x['find'] = $find = filter_input(INPUT_POST, 'find', FILTER_SANITIZE_SPECIAL_CHARS);
        $sql = file_get_contents($this->root . '/sql/user_find.sql');
        $this->x['users'] = $this->db->getAllFromSQL($sql, ['find' => $find]);

        $this->twig->showTemplate('adminka/users.html', ['x' => $this->x, 'my' => $this->myUser]);
    }

    public function user ($guid): void
    {
        $sql = file_get_contents($this->root . '/sql/adminka/get_user.sql');
        $this->x['user'] = $this->db->getOneRowFromSQL($sql, ['guid' => $guid]);
        $this->x['works'] = $this->db->getKeyValue('id, name', 'PIKALKA.d_pass_info', [],'id');
//        vd($this->x['works']);
        $this->twig->showTemplate('adminka/user.html', ['x' => $this->x, 'my' => $this->myUser]);
    }
}