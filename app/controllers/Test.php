<?php

namespace App\controllers;

class Test
{
    protected $db;

    public function __construct (\App\QueryBuilder $db)
    {
        $this->db = $db;
    }

    public function index (): void
    {
        $sql = 'TRUNCATE TABLE a123';
        $this->db->runSQL($sql);
        $this->db->beginTransaction();
        $this->db->insert('a123', ['id' => '222']);
        $this->db->insert('a123', ['id' => '111']);
        $this->db->endTransaction();
    }
}