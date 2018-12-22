<?php
/** @noinspection ClassConstantCanBeUsedInspection */

if (!session_id()) {@session_start();}
header('Content-Type: text/html; charset=windows-1251');

require_once '../config/main.php';
require_once '../app/functions.php';
require_once '../vendor/autoload.php';

use DI\ContainerBuilder;
$containerBuilder = new ContainerBuilder;
$containerBuilder->addDefinitions([
    Twig_Environment::class => function() {
        $loader = new \Twig_Loader_Filesystem('template');
        return new \Twig_Environment($loader, ['charset' => 'windows-1251']);
    },

    PDO::class => function() {
        $dbconfig = require '../config/config_ora.php';
        return new \PDO('oci:dbname='.$dbconfig['oracle_tns'], $dbconfig['username'], $dbconfig['password'], $dbconfig['pdo_options']);
    }
]);

try {
    $container = $containerBuilder->build();
} catch (\Exception $e) {
    echo $e->getMessage();
}

$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/', ['App\controllers\Home', 'index']);
    $r->addRoute('GET', '/refresh', ['App\MyUser', 'refresh']);
    $r->addRoute('GET', '/logout', ['App\MyUser', 'logout']);
    $r->addRoute('GET', '/about', ['App\controllers\Home', 'about']);
    $r->addRoute('GET', '/test', ['App\controllers\Test', 'index']);
    $r->addRoute('GET', '/pasport', ['App\controllers\Pasport', 'pasport']);
    $r->addRoute('GET', '/pasport/job', ['App\controllers\Pasport', 'job']);
    $r->addRoute('POST', '/pasport/check', ['App\controllers\Pasport', 'check']);
    $r->addRoute('GET', '/pasport/prepare', ['App\controllers\Pasport', 'prepare']);
    $r->addRoute('POST', '/pasport/prepare', ['App\controllers\Pasport', 'prepare']);
    $r->addRoute('POST', '/pasport/excel', ['App\controllers\Pasport', 'toExcel']);
    $r->addRoute('GET', '/token/{token:[0-9a-zA-Z]{64}}', ['App\controllers\Token', 'index']);
    // {id} must be a number (\d+)
    $r->addRoute('GET', '/user/{id:\d+}', 'get_user_handler');
    // The /{title} suffix is optional
    $r->addRoute('GET', '/articles/{id:\d+}[/{title}]', 'get_article_handler');
});

$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

if (false !== $pos = strpos($uri, '?')) {$uri = substr($uri, 0, $pos);}
$uri = rawurldecode($uri);
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