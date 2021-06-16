<?php
/** @noinspection ClassConstantCanBeUsedInspection */

if (!session_id()) {@session_start();}
header('Content-Type: text/html; charset=windows-1251');
date_default_timezone_set('Europe/Kiev');

define('ROOT', $_SERVER['DOCUMENT_ROOT']);

require_once ROOT . '/config/main.php';
require_once ROOT . '/app/functions.php';
require_once ROOT . '/vendor/autoload.php';

use DI\ContainerBuilder;
$containerBuilder = new ContainerBuilder;

$containerBuilder->addDefinitions([

    Twig\Environment::class => static function() {
        $loader = new \Twig\Loader\FilesystemLoader('template');
        return new \Twig\Environment($loader, ['charset' => 'windows-1251']);
    },

    PDO::class => static function() {
        $db_config = require ROOT . '/app/other/oracle_connect.php';
        try {
            return new \PDO('oci:dbname='.$db_config['oracle_tns'], $db_config['username'], $db_config['password'], $db_config['pdo_options']);
        } catch (\PDOException $e) {
            echo '<h3>' . $e->getMessage() . '</h3>';
        }
        return null;
    }
]);

try {
    $container = $containerBuilder->build();
} catch (\Exception $e) {
    echo $e->getMessage();
}

require ROOT . '/config/routes.php';

$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

if (false !== $pos = strpos($uri, '?')) {$uri = substr($uri, 0, $pos);}
$uri = rawurldecode($uri);
/** @noinspection PhpUndefinedVariableInspection */
$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        $container->call(['App\controllers\Error', 'e404']); break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        $container->call(['App\controllers\Error', 'e405']); break;
    case FastRoute\Dispatcher::FOUND:
        $container->call($routeInfo[1], $routeInfo[2]); break;
}
