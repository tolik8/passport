<?php

namespace App;

use PHPUnit\Framework\TestCase;

$root = require 'root.php';
require $root . '../app/Logger.php';
require $root . '../app/QueryBuilder.php';

class QueryBuilderTest extends TestCase
{
    private $QB;

    protected function setUp()
    {
        $root = require 'root.php';
        $logger = new Logger($root);
        $dbconfig = require $root . '../config/config_ora.php';
        $pdo = new \PDO('oci:dbname='.$dbconfig['oracle_tns'], $dbconfig['username'], $dbconfig['password'], $dbconfig['pdo_options']);
        $this->QB = new QueryBuilder($pdo, $logger);
    }

    protected function tearDown()
    {
        $this->QB = NULL;
    }

    public function testGetCount()
    {
        $result = $this->QB->getCount('PIKALKA.people', ['viddil_id' => '19-00-09-01']);
        $this->assertEquals(4, $result);
    }

    public function testGetOneValue()
    {
        $result = $this->QB->getOneValue('login', 'PIKALKA.people', ['guid' => '06F2EF58972B2E32E050130A64136A5F']);
        $this->assertEquals('admin19t', $result);
        /*
        assertTrue, assertFalse
        assertEmpty, assertEquals
        assertCount, assertContains
        */
    }
}