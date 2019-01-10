<?php

$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/', ['App\controllers\Home', 'index']);
    $r->addRoute('GET', '/refresh', ['App\MyUser', 'refresh']);
    $r->addRoute('GET', '/logout', ['App\MyUser', 'logout']);
    $r->addRoute('GET', '/about', ['App\controllers\Home', 'about']);
    $r->addRoute('GET', '/test', ['App\controllers\Test', 'index']);
    $r->addRoute('GET', '/pasport', ['App\controllers\Pasport', 'pasport']);
//    $r->addRoute('GET', '/pasport/job', ['App\controllers\Pasport', 'job']);
    $r->addRoute('POST', '/pasport/check', ['App\controllers\Pasport', 'check']);
    $r->addRoute('GET', '/pasport/prepare', ['App\controllers\Pasport', 'prepare']);
    $r->addRoute('POST', '/pasport/prepare', ['App\controllers\Pasport', 'prepare']);
    $r->addRoute('GET', '/pasport/loading/{guid:[0-9a-zA-Z]{1,32}}', ['App\controllers\Pasport', 'loading']);
    $r->addRoute('GET', '/pasport/ajax/{guid:[0-9a-zA-Z]{1,32}}', ['App\controllers\Pasport', 'ajax']);
    $r->addRoute('POST', '/pasport/excel', ['App\controllers\Pasport', 'toExcel']);
    $r->addRoute('GET', '/token/{token:[0-9a-zA-Z]{64}}', ['App\controllers\Token', 'index']);
    // {id} must be a number (\d+)
    $r->addRoute('GET', '/user/{id:\d+}', 'get_user_handler');
    // The /{title} suffix is optional
    $r->addRoute('GET', '/articles/{id:\d+}[/{title}]', 'get_article_handler');
});