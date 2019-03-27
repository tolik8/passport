<?php

return [
    'adminka' => [
        'link' => '/adminka',
        'name' => 'Адмінка',
        'par' => 'index',
    ],
    'passport' => [
        'link' => '/adminka/passport',
        'name' => 'Доступ до паспорта',
        'par' => 'adminka',
    ],
    'users' => [
        'link' => '/adminka/users',
        'name' => 'Користувачі',
        'par' => 'passport',
    ],
    'user' => [
        'link' => '/adminka/user',
        'name' => 'Користувач',
        'par' => 'passport',
    ],
];
