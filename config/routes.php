<?php
/** @noinspection ClassConstantCanBeUsedInspection */

$dispatcher = FastRoute\simpleDispatcher(static function(FastRoute\RouteCollector $r) {
    //$r->get('/', ['App\controllers\Home', 'index']);
    $r->get('/cookie/{cookie:[0-9a-zA-Z]{64}}', ['App\controllers\Cookie', 'index']);
    $r->get('/refresh', ['App\MyUser', 'refresh']); // погано викликати замість контроллера обєкт
    $r->get('/logout', ['App\MyUser', 'logout']);   // погано викликати замість контроллера обєкт
    $r->get('/test', ['App\controllers\TestExcel', 'index']);
    $r->get('/test/export', ['App\controllers\TestExcel', 'export']);

    //$r->get('/passport', ['App\controllers\Passport', 'index']);
    $r->get('/', ['App\controllers\Passport', 'index']);
    $r->post('/passport/choice', ['App\controllers\Passport', 'choice']);
    $r->post('/passport/prepare', ['App\controllers\Passport', 'prepare']);
    $r->get('/passport/ajax/{guid:[0-9a-zA-Z]{1,32}}', ['App\controllers\Passport', 'ajax']);

    $r->post('/passport/check', ['App\controllers\Passport', 'check']);
    //$r->addRoute(['GET', 'POST'], '/passport/prepare', ['App\controllers\Passport', 'prepare']);
    $r->get('/passport/loading/{guid:[0-9a-zA-Z]{1,32}}', ['App\controllers\Passport', 'loading']);
    $r->get('/passport/taxpayer_not_found', ['App\controllers\Passport', 'taxpayer_not_found']);
    $r->post('/passport/excel', ['App\controllers\PassToExcel', 'index']);

    $r->addGroup('/adminka', function (FastRoute\RouteCollector $r) {
        $r->addRoute('GET', '', ['App\controllers\Adminka', 'index']);
        $r->addRoute('GET', '/passport', ['App\controllers\Adminka', 'passport']);
        $r->addRoute('POST', '/passport/users', ['App\controllers\Adminka', 'users']);
        $r->addRoute('GET', '/passport/user/{guid:[0-9a-zA-Z]{1,32}}', ['App\controllers\Adminka', 'user']);
        $r->addRoute('POST', '/passport/update', ['App\controllers\Adminka', 'passport_access_update']);
        $r->addRoute('POST', '/user_find', ['App\controllers\Adminka', 'user_find']);
    });

});
