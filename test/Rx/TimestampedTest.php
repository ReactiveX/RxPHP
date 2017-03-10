<?php

declare(strict_types = 1);

namespace Rx;

class TimestampedTest extends TestCase
{
    public function testEqualWithScalar()
    {
        $ts1 = new Timestamped(123, "Hello");
        $ts2 = new Timestamped(123, "Hello");
        
        $this->assertTrue($ts1->equals($ts2));
    }

    public function testNotEqualWithScalar()
    {
        $ts1 = new Timestamped(123, "Hi");
        $ts2 = new Timestamped(123, "Hello");

        $this->assertTrue(!$ts1->equals($ts2));
    }

    public function testNotEqualInTime()
    {
        $ts1 = new Timestamped(124, "Hello");
        $ts2 = new Timestamped(123, "Hello");

        $this->assertTrue(!$ts1->equals($ts2));
    }

    public function testEqualNotATimestamp()
    {
        $ts1 = new Timestamped(123, "Hello");

        $this->assertTrue(!$ts1->equals("Hello"));
    }

    public function testEqualSameTimestamp()
    {
        $ts1 = new Timestamped(123, "Hello");

        $this->assertTrue($ts1->equals($ts1));
    }

    public function testEqualObjectSameInstance()
    {
        $o1 = new \stdClass();
        $o1->x = "y";
        $o2 = $o1;
        
        $ts1 = new Timestamped(123, $o1);
        $ts2 = new Timestamped(123, $o2);

        $this->assertTrue($ts1->equals($ts2));
    }

    public function testNotEqualObjectDiffentInstance()
    {
        $o1 = new \stdClass();
        $o1->x = "y";
        
        $o2 = clone $o1;

        $ts1 = new Timestamped(123, $o1);
        $ts2 = new Timestamped(123, $o2);

        $this->assertTrue(!$ts1->equals($ts2));
    }

    public function testGetValue()
    {
        $ts1 = new Timestamped(123, "Hello");

        $this->assertEquals("Hello", $ts1->getValue());
    }
}
