<?php

$dbconfig = [
    'username'    => 'username',
    'password'    => 'password',
    'host'        => 'host',
    'port'        => '1521',
    'service'     => 'service',
    'pdo_options' => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]
];

$oracle_tns = '
(DESCRIPTION =
    (ADDRESS_LIST =
        (ADDRESS = (PROTOCOL = TCP)(HOST = '.$dbconfig['host'].')(PORT = '.$dbconfig['port'].'))
    )
    (CONNECT_DATA =
        (SERVICE_NAME = '.$dbconfig['service'].')
    )
)
';

$dbconfig['oracle_tns'] = $oracle_tns;

return $dbconfig;
