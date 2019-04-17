<?php

namespace App;

class Breadcrumb
{
    protected $menu1;
    public $isUnderConstruct = false;

    public function __construct ()
    {
        $pattern = '#(?<=^/).+?(?=/|$)#';
        preg_match($pattern, $_SERVER['REQUEST_URI'], $matches);
        if (count($matches) > 0) {$project = $matches[0];} else {$project = '';}

        $first_menu['index'] = [
            'link' => '/',
            'name' => 'Головна',
            'par' => 'root',
        ];

        $filemenu = ROOT . '/config/menu/' . $project . '.php';
        if (file_exists($filemenu)) {
            /** @noinspection PhpIncludeInspection */
            $menu = require $filemenu;
            /** @noinspection PhpUndefinedVariableInspection */
            /** @noinspection IssetArgumentExistenceInspection */
            if (isset($isUnderConstruct)) {$this->isUnderConstruct = $isUnderConstruct;}
        } else {
            $menu = [];
        }

        $this->menu1 = array_merge($first_menu, $menu);
    }

    public function getMenu ($page_id): array
    {
        $menu2 = [];
        $n = 0;
        while ($page_id !== 'root') {
            $n++; if ($n === 100) {break;}
            array_unshift($menu2, $this->menu1[$page_id]);
            $page_id = $this->menu1[$page_id]['par'];
        }
        $menu2[count($menu2)-1]['active'] = 1;

        return $menu2;
    }
}