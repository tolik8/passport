<?php

namespace App;

class Breadcrumb
{
    protected $menu1;

    public function __construct ()
    {
        $pattern = '#(?<=^/).+?(?=/|$)#';
        preg_match($pattern, $_SERVER['REQUEST_URI'], $matches);
        if (count($matches) > 0) {$project = $matches[0];} else {$project = '';}
        
        $menu['index']['link'] = '/';
        $menu['index']['name'] = 'Головна';
        $menu['index']['par'] = 'root';

        if (file_exists('../config/menu/'.$project.'.php')) {
            require_once '../config/menu/'.$project.'.php';
        }
        $this->menu1 = $menu;
    }

    public function getMenu ($page_id)
    {
        $menu2 = [];
        $n = 0;
        while ($page_id != 'root') {
            $n++; if ($n == 100) {break;}
            array_unshift($menu2, $this->menu1[$page_id]);
            $page_id = $this->menu1[$page_id]['par'];
        }
        $menu2[count($menu2)-1]['active'] = 1;

        return $menu2;
    }
}