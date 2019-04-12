<?php
/** @noinspection PhpUndefinedMethodInspection */

namespace App;

include 'begin.php';

use PHPUnit\Framework\TestCase;

/* assertTrue, assertFalse, assertEmpty, assertEquals, assertCount, assertContains */

class QueryBuilder2Test extends TestCase
{
    private $db;
    private $root;

    protected function setUp()
    {
        $root = $_SERVER['DOCUMENT_ROOT'];
        if ($root === '') {$root = 'D:/www/alisa2.loc';}
        $dbconfig = include $root . '/config/config_ora.php';
        $pdo = new \PDO('oci:dbname='.$dbconfig['oracle_tns'], $dbconfig['username'], $dbconfig['password'], $dbconfig['pdo_options']);
        $this->db = new QueryBuilder2($pdo);
        $this->root = $root;
    }

    protected function tearDown()
    {
        $this->db = null;
    }

    /* select, bind, get, (getSQK, execute) */
    public function testSelectGet(): void
    {
        $assert = false;
        $need_result = [
            ['ID' => '1', 'NAME' => 'відпустка'],
            ['ID' => '2', 'NAME' => 'відрядження'],
            ['ID' => '3', 'NAME' => 'лікарняний'],
        ];
        $data = ['id' => 4];
        $sql = 'SELECT * FROM PIKALKA.d_absence WHERE id < :id ORDER BY id';
        $result = $this->db->select($sql)->bind($data)->get();
        if ($need_result === $result) {$assert = true;}
        $this->assertTrue($assert);
    }

    /* table, field, where, groupBy, having, orderBy, getSQL */
    public function testConstructorGetSQL(): void
    {
        $assert = false;
        $need_result = file_get_contents($this->root . '\tests\App\inc\testConstructorGetSQL.sql');

        $result = $this->db->table('PIKALKA.people')
            ->field('kadry_id, COUNT(*) cnt')
            ->where('kadry_id = :id')
            ->groupBy('kadry_id')
            ->having('COUNT(*) > 1')
            ->orderBy('kadry_id')
            ->getSQL();
        if ($need_result === $result) {$assert = true;}
        $this->assertTrue($assert);
    }

    public function testFirst(): void
    {
        $assert = false;
        $need_result = ['ID' => '1', 'NAME' => 'відпустка'];
        $sql = 'SELECT * FROM PIKALKA.d_absence ORDER BY id';
        $result = $this->db->select($sql)->first();
        if ($need_result === $result) {$assert = true;}
        $this->assertTrue($assert);
    }

    public function testPluck(): void
    {
        $assert = false;
        $need_result = ['1', '2', '3', '4'];
        $sql = 'SELECT * FROM PIKALKA.d_absence ORDER BY id';
        $result = $this->db->select($sql)->pluck('ID');
        if ($need_result === $result) {$assert = true;}
        $this->assertTrue($assert);
    }

    public function testGetCell(): void
    {
        $assert = false;
        $need_result = 'відпустка';
        $sql = 'SELECT name FROM PIKALKA.d_absence WHERE id = 1';
        $result = $this->db->select($sql)->getCell();
        if ($need_result === $result) {$assert = true;}
        $this->assertTrue($assert);
    }
}