<?php

namespace App;

class Helper
{
    public static function in_string($string, $find): bool
    {
        $array = explode(',', $string);
        return in_array($find, $array, true);
    }

    public static function getPattern($pattern)
    {
        $patterns = [
            'guid' => '#^[0-9a-zA-Z]{32}$#',
            'tin' => '#^[0-9]{6,10}$#',
            'date' => '#^\s*(3[01]|[12][0-9]|0?[1-9])\.(1[012]|0?[1-9])\.((?:19|20)\d{2})\s*$#',
            'list' => '#^(([0-9]+,)+[0-9]+)|[0-9]+$#',
        ];
        return $patterns[$pattern] ?? null;
    }

    public static function regEx($pattern, $post, $default = null)
    {
        if (preg_match($pattern, $post)) {
            return $post;
        }
        return $default;
    }

    /** @noinspection ReturnTypeCanBeDeclaredInspection */
    public static function checkRegEx($pattern_name, $post, $default = null)
    {
        $pattern = self::getPattern($pattern_name);

        if ($pattern === null) {
            return null;
        }
        return self::RegEx($pattern, $post, $default);
    }

    public static function utf8($input)
    {
        return mb_convert_encoding($input, 'utf-8', 'windows-1251');
    }

    public static function cp1251($input)
    {
        return mb_convert_encoding($input, 'windows-1251', 'utf-8');
    }

    /* Получить из массива из базы другой массив, где ключем будет первое поле, а остальные массив значений */
    public static function array_combine2(array $array): array
    {
        if (empty($array)) {return [];}
        $result = [];
        $first_column = key($array[0]);

        foreach ($array as $row) {
            $first_value = $row[$first_column];
            foreach ($row as $key => $item) {
                if ($key !== $first_column) {$result[$first_value][$key] = $item;}
            }
        }
        return $result;
    }

    public static function arrayToUtf8(array $array): array
    {
        array_walk_recursive($array, static function(&$item) {
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
