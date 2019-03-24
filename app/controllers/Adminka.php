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
        $find = $_POST['query'];
        $sql = file_get_contents($this->root . '/sql/user_find.sql');
        $res = $this->db->getAllFromSQL($sql, ['find' => cp1251($find)]);
        $users = ['suggestions' => $res];
        /** @noinspection PhpComposerExtensionStubsInspection */
        echo json_encode(ArrayToUtf8($users));
    }

}