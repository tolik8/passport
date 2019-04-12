<?php

namespace App\controllers;

use App\QueryBuilder2;

class TestQB
{
    protected $db;
    protected $tables;

    public function __construct (QueryBuilder2 $db)
    {
        $this->db = $db;
    }

    public function index (): void
    {
        $db = $this->db;

        //$data = ['viddil_id' => '19-00-09-01'];

        $res1 = $db->table('PIKALKA.people')
            ->field('kadry_id, COUNT(*) cnt')
            //->where('viddil_id = :viddil_id')
            ->groupBy('kadry_id')
            ->having('COUNT(*) > 1')
            //->orderBy('fio1')
            //->bind($data)
            ->get();
        vd($res1);

        $res2 = $db->table('PIKALKA.d_pass_task')->first();
        vd($res2);

        $sql = 'SELECT viddil_id, login, fio1, fio2, fio3 FROM PIKALKA.people WHERE kab = :kab';
        $res3 = $db->select($sql, ['kab' => 1001])->first();
        vd($res3);
    }

}