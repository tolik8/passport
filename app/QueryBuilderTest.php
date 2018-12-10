<?php

namespace App;

use PHPUnit\Framework\TestCase;

require 'Logger.php';
require 'QueryBuilder.php';

class QueryBuilderTest extends TestCase
{
    private $qb;

    protected function setUp()
    {
        $logger = new Logger();
        $dbconfig = require '../config/select_db.php';
        $pdo = new \PDO('oci:dbname='.$dbconfig['oracle_tns'], $dbconfig['username'], $dbconfig['password'], $dbconfig['pdo_options']);
        $this->qb = new QueryBuilder($pdo, $logger);
    }

    protected function tearDown()
    {
        $this->qb = NULL;
    }

    public function testGetCount()
    {
        $result = $this->qb->getCount('PIKALKA.people');
        $this->qb(766, $result);
    }

}