<?php

namespace App;

class Config
{
    public static function get($path)
    {
        $array = explode('.', $path);
        $file = ROOT . '/config/'.trim($array[0]).'.php';
        $key = trim($array[1]);
        if (!file_exists($file)) {return null;}
        $data = include $file;
        if (!isset($data[$key])) {return null;}
        return $data[$key];
    }

}
