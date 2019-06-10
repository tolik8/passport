<?php

const DEBUG = true;
define('ROOT', $_SERVER['DOCUMENT_ROOT']);

if (DEBUG) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}
