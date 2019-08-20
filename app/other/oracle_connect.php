<?php

$db_config = require ROOT . '/config/config_ora.php';

$oracle_tns = '
(DESCRIPTION =
    (ADDRESS_LIST =
        (ADDRESS = (PROTOCOL = TCP)(HOST = '.$db_config['host'].')(PORT = '.$db_config['port'].'))
    )
    (CONNECT_DATA =
        (SERVICE_NAME = '.$db_config['service'].')
    )
)
';

$db_config['oracle_tns'] = $oracle_tns;

return $db_config;
