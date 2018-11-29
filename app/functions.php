<?php

function vd ($input)
{
    echo '<pre>'; var_dump($input); echo '</pre>';
}

function dd ($input)
{
    echo '<pre>'; var_dump($input); echo '</pre>'; die;
}

function in_string ($find, string $line_separated)
{
    $result = false;
    $array = explode(',', $line_separated);
    if (in_array($find, $array)) {$result = true;}
    return $result;
}

function regex ($pattern, $subject, $default = null)
{
    if (preg_match($pattern, $subject)) {$result = $subject;} else {$result = $default;}
    return $result;
}