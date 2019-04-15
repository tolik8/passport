<?php
/** @noinspection PhpUndefinedMethodInspection */

namespace App;

use PHPUnit\Framework\TestCase;

include 'begin.php';

/* assertTrue, assertFalse, assertEmpty, assertEquals, assertCount, assertContains */

class QueryBuilderTest extends TestCase
{
    private $db;

    protected function setUp()
    {
        $root = $_SERVER['DOCUMENT_ROOT'];
        if ($root === '') {$root = 'D:/www/alisa2.loc';}
        $dbconfig = require $root . '/config/config_ora.php';
        $pdo = new \PDO('oci:dbname='.$dbconfig['oracle_tns'], $dbconfig['username'], $dbconfig['password'], $dbconfig['pdo_options']);
        $this->db = new QueryBuilder($pdo);
    }

    protected function tearDown()
    {
        $this->db = NULL;
    }

    public function testGetAll(): void
    {
        $result = $this->db->getAll('PIKALKA.people');
        $count = count($result);
        if ($count > 700) {$countRes = true;} else {{$countRes = false;}}
        $this->assertTrue($countRes);
    }

    public function testGetCount(): void
    {
        $result = $this->db->getCount('PIKALKA.people', ['viddil_id' => '19-00-09-01']);
        $this->assertEquals(4, $result);
    }

    public function testGetOneValue(): void
    {
        $result = $this->db->getOneValue('login', 'PIKALKA.people', ['guid' => '06F2EF58972B2E32E050130A64136A5F']);
        $this->assertEquals('admin19t', $result);
    }

    /*public function testExecute()
    {
        $sql = 'SELECT sys_guid() FROM dual';
        $qb = selft::execute($sql);
        $result = $this->QB->execute($sql);
        var_dump($result);
        //$this->assertEquals('admin19t', $result);
    }*/
}