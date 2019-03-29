<?php

function vd ($input)
{
    echo '<pre>'; var_dump($input); echo '</pre>';
}

function dd ($input)
{
    echo '<pre>'; var_dump($input); echo '</pre>'; die;
}

function getSQL ($path)
{
    $sql_file = $_SERVER['DOCUMENT_ROOT'] . '/sql/' . $path;
    if (file_exists($sql_file)) {
        $content = file_get_contents($sql_file);
    } else {
        $content = 'File not found: ' . $sql_file;
    }

    return $content;
}