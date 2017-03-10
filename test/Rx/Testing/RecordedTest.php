<?php

declare(strict_types = 1);

namespace Rx\Testing;

use Rx\TestCase;

class RecordedTest extends TestCase
{
    public function testRecordedWillUseStrictCompareIfNoEqualsMethod()
    {
        $recorded1 = new Recorded(1, 5);
        $recorded2 = new Recorded(1, "5");
        $recorded3 = new Recorded(1, 5);

        $this->assertFalse($recorded1->equals($recorded2));
        $this->assertTrue($recorded1->equals($recorded3));
    }

    public function testRecordedToString()
    {
        $recorded = new Recorded(1, 5);

        $this->assertEquals("5@1", $recorded->__toString());
    }
}
