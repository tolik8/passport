<?php

namespace App;

class Tax
{
    private $db;

    public function __construct (QueryBuilder $db)
    {
        $this->db = $db;
    }

    public function getName ($tin): string
    {
        $sql = 'SELECT name FROM RG02.r21taxpay WHERE tin = :tin AND c_distr = PIKALKA.tax.get_dpi_by_tin(:tin)';
        return $this->db->getOneValueFromSQL($sql, ['tin' => $tin]);
    }
}