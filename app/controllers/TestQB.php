<?php

namespace App\controllers;

use App\QueryBuilder2;

class TestQB
{
    protected $qb;
    protected $tables;

    public function __construct (QueryBuilder2 $qb)
    {
        $this->qb = $qb;
    }

    public function index (): void
    {
        $data = ['viddil_id' => '19-00-09-01'];

        $rows = $this->qb->table('PIKALKA.people')
            ->select('viddil_id, login, fio1, fio2, fio3')
            ->where('viddil_id = :viddil_id')
            //->orderBy('fio1')
            ->getValue('fio1', $data);

        vd($this->qb->getSQL());
        vd($rows);
    }

}