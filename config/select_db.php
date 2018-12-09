<?php

$root = $_SERVER['DOCUMENT_ROOT'];

switch ($_SERVER['SERVER_ADMIN']) {

    case 'admin@start.tr.sta':
        return require $root . '/config/db/config_server.php';
        break;

    case 'admin19t@start.tr.sta':
        return require $root . '/config/db/config_dev.php';
        break;

    case 'zevs@start.tr.sta':
        return require $root . '/config/db/config_zevs.php';
        break;

    default:
        exit('The correct ServerAdmin parameter was not found in Apache. Set e-mail in the ServerAdmin parameter.');
}