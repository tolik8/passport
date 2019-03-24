<?php

function vd ($input)
{
    echo '<pre>'; var_dump($input); echo '</pre>';
}

function dd ($input)
{
    echo '<pre>'; var_dump($input); echo '</pre>'; die;
}

function in_string ($find, $line_separated)
{
    $result = false;
    $array = explode(',', $line_separated);
    if (in_array($find, $array, true)) {$result = true;}
    return $result;
}

function regex ($pattern, $subject, $default = null)
{
    if (preg_match($pattern, $subject)) {$result = $subject;} else {$result = $default;}
    return $result;
}

function utf8 ($input)
{
    return mb_convert_encoding($input, 'utf-8', 'windows-1251');
}

function cp1251 ($input)
{
    return mb_convert_encoding($input, 'windows-1251', 'utf-8');
}

function ArrayToUtf8 (array $array)
{
    array_walk_recursive($array, function(&$item, $key){
        if(!mb_detect_encoding($item, 'utf-8', true)){
//            $item = utf8_encode($item);
            $item = mb_convert_encoding($item, 'utf-8', 'windows-1251');
        }
    });

    return $array;
}