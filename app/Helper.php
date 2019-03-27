<?php

namespace App;

class Helper
{
    public static function in_string ($string, $find): bool
    {
        $array = explode(',', $string);
        return in_array($find, $array, true);
    }

    public static function regex ($pattern, $subject, $default = null)
    {
        if (preg_match($pattern, $subject)) {$result = $subject;} else {$result = $default;}
        return $result;
    }

    public static function utf8 ($input)
    {
        return mb_convert_encoding($input, 'utf-8', 'windows-1251');
    }

    public static function cp1251 ($input)
    {
        return mb_convert_encoding($input, 'windows-1251', 'utf-8');
    }

    public static function ArrayToUtf8 (array $array): array
    {
        array_walk_recursive($array, function(&$item) {
            if(!mb_detect_encoding($item, 'utf-8', true)){
                $item = mb_convert_encoding($item, 'utf-8', 'windows-1251');
            }
        });
        return $array;
    }

    public static function getArrayIdFromPost($post, string $prefix): array
    {
        $result = [];
        foreach ($post as $key => $item) {
            if (strpos($key, $prefix) === 0) {
                $result[] = substr($key, strlen($prefix));
            }
        }
        return $result;
    }
}