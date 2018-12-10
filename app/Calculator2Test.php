<?php

use PHPUnit\Framework\TestCase;

require 'Calculator2.php';

class Calculator2Test extends TestCase
{
    private $calculator;

    protected function setUp()
    {
        $this->calculator = new Calculator2();
    }

    protected function tearDown()
    {
        $this->calculator = NULL;
    }

    public function testAdd()
    {
        $result = $this->calculator->add(10, 4);
        $this->assertEquals(14, $result);
    }

    public function testDob()
    {
        $result = $this->calculator->dob(5, 5);
        $this->assertEquals(25, $result);
    }

}