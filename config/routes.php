<?php

$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $r->get('/', ['App\controllers\Home', 'index']);
    $r->get('/refresh', ['App\MyUser', 'refresh']);
    $r->get('/logout', ['App\MyUser', 'logout']);
    $r->get('/about', ['App\controllers\Home', 'about']);
    $r->get('/test', ['App\controllers\Test', 'index']);
    
    $r->get('/passport', ['App\controllers\Passport', 'index']);
//    $r->get('/passport/job', ['App\controllers\Passport', 'job']);
    $r->post('/passport/check', ['App\controllers\Passport', 'check']);
    $r->addRoute(['GET', 'POST'], '/passport/prepare', ['App\controllers\Passport', 'prepare']);
    $r->get('/passport/loading/{guid:[0-9a-zA-Z]{1,32}}', ['App\controllers\Passport', 'loading']);
    $r->get('/passport/ajax/{guid:[0-9a-zA-Z]{1,32}}', ['App\controllers\Passport', 'ajax']);
    $r->post('/passport/excel', ['App\controllers\Passport', 'toExcel']);
    
    $r->get('/token/{token:[0-9a-zA-Z]{64}}', ['App\controllers\Token', 'index']);
});
