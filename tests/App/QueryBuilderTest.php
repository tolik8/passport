<?php
/** @noinspection PhpUndefinedMethodInspection */

namespace App;

use PHPUnit\Framework\TestCase;
use PDO;

if ($_SERVER['DOCUMENT_ROOT'] === '') {$_SERVER['DOCUMENT_ROOT'] = 'D:/www/alisa2.loc';}

require_once $_SERVER['DOCUMENT_ROOT'] . '/config/main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/functions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

/* assertTrue, assertFalse, assertEmpty, assertEquals, assertCount, assertContains */

class QueryBuilderTest extends TestCase
{
    private $QB;

    protected function setUp()
    {
        $dbconfig = require $_SERVER['DOCUMENT_ROOT'] . '/config/config_ora.php';
        $pdo = new PDO('oci:dbname='.$dbconfig['oracle_tns'], $dbconfig['username'], $dbconfig['password'], $dbconfig['pdo_options']);
        $this->QB = new QueryBuilder($pdo);
    }

    protected function tearDown()
    {
        $this->QB = NULL;
    }

    public function testGetAll(): void
    {
        $result = $this->QB->getAll('PIKALKA.people');
        $count = count($result);
        if ($count > 700) {$countRes = true;} else {{$countRes = false;}}
        $this->assertTrue($countRes);
    }

    public function testGetCount(): void
    {
        $result = $this->QB->getCount('PIKALKA.people', ['viddil_id' => '19-00-09-01']);
        $this->assertEquals(4, $result);
    }

    public function testGetOneValue(): void
    {
        $result = $this->QB->getOneValue('login', 'PIKALKA.people', ['guid' => '06F2EF58972B2E32E050130A64136A5F']);
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