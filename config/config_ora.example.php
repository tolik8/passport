<?php

return [
    'username'    => 'ORACLE_USERNAME',
    'password'    => 'ORACLE_PASSWORD',
    'host'        => 'ORACLE_SERVER_IP',
    'port'        => '1521',
    'service'     => 'TNS_SERVICE',
    'pdo_options' => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]
];
