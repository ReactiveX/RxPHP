<?php

declare(strict_types = 1);

namespace Rx;

class TimestampedTest extends TestCase
{
    public function testEqualWithScalar(): void
    {
        $ts1 = new Timestamped(123, "Hello");
        $ts2 = new Timestamped(123, "Hello");
        
        $this->assertTrue($ts1->equals($ts2));
    }

    public function testNotEqualWithScalar(): void
    {
        $ts1 = new Timestamped(123, "Hi");
        $ts2 = new Timestamped(123, "Hello");

        $this->assertTrue(!$ts1->equals($ts2));
    }

    public function testNotEqualInTime(): void
    {
        $ts1 = new Timestamped(124, "Hello");
        $ts2 = new Timestamped(123, "Hello");

        $this->assertTrue(!$ts1->equals($ts2));
    }

    public function testEqualNotATimestamp(): void
    {
        $ts1 = new Timestamped(123, "Hello");

        $this->assertTrue(!$ts1->equals("Hello"));
    }

    public function testEqualSameTimestamp(): void
    {
        $ts1 = new Timestamped(123, "Hello");

        $this->assertTrue($ts1->equals($ts1));
    }

    public function testEqualObjectSameInstance(): void
    {
        $o1 = new \stdClass();
        $o1->x = "y";
        $o2 = $o1;
        
        $ts1 = new Timestamped(123, $o1);
        $ts2 = new Timestamped(123, $o2);

        $this->assertTrue($ts1->equals($ts2));
    }

    public function testNotEqualObjectDiffentInstance(): void
    {
        $o1 = new \stdClass();
        $o1->x = "y";
        
        $o2 = clone $o1;

        $ts1 = new Timestamped(123, $o1);
        $ts2 = new Timestamped(123, $o2);

        $this->assertTrue(!$ts1->equals($ts2));
    }

    public function testGetValue(): void
    {
        $ts1 = new Timestamped(123, "Hello");

        $this->assertEquals("Hello", $ts1->getValue());
    }
}
