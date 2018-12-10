<?php

use PHPUnit\Framework\TestCase;

$root = require 'root.php';
require $root . '../app/Calculator.php';

class CalculatorTest extends TestCase
{
    private $calc;

    protected function setUp()
    {
        $this->calc = new Calculator();
    }

    protected function tearDown()
    {
        $this->calc = NULL;
    }

    public function testAdd()
    {
        $result = $this->calc->add(1, 2);
        $this->assertEquals(3, $result);
    }

    public function testDob()
    {
        $result = $this->calc->dob(5, 5);
        $this->assertEquals(25, $result);
    }

}