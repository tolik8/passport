<?php

namespace App;

class Tax
{
    private $db;

    public function __construct(QueryBuilder $db)
    {
        $this->db = $db;
    }

    public function getName($tin)
    {
        $sql = 'SELECT name FROM RG02.r21taxpay WHERE tin = :tin AND c_distr = TOLIK.tax.get_dpi_by_tin(:tin)';
        return $this->db->selectRaw($sql, ['tin' => $tin])->getCell();
    }

}
