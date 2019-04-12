<?php

$root = $_SERVER['DOCUMENT_ROOT'];
if ($root === '') {$root = 'D:/www/alisa2.loc';}

include $root . '/config/main.php';
include $root . '/app/functions.php';
include $root . '/vendor/autoload.php';
