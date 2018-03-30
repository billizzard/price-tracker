<?php
namespace App\Tests\phpunit\Price;

class PriceCheckerTest extends DataFixturesTestCase
{
    public function testPushAndPop()
    {
        $stack = [];
        $this->assertEquals(0, count($stack));
    }

}