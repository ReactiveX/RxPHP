<?php

declare(strict_types = 1);

namespace Rx\Scheduler;

use Rx\TestCase;

class ImmediateSchedulerTest extends TestCase
{
    /**
     * @test
     */
    public function now_returns_the_time()
    {
        $scheduler = new ImmediateScheduler();

        $this->assertTrue(abs(time() * 1000 - $scheduler->now()) < 1000, 'time difference is less than or equal to 1');
    }

    /**
     * @test
     */
    public function non_zero_delay_throws()
    {
        $this->expectException(\Exception::class);
        $this->expectDeprecationMessage('ImmediateScheduler does not support a non-zero delay.');
        $scheduler = new ImmediateScheduler();

        $scheduler->schedule(function () {
            $this->fail("This should never get called");
        }, 1);
    }

    /**
     * @test
     */
    public function schedule_periodic_throws()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('ImmediateScheduler does not support a non-zero delay.');
        $scheduler = new ImmediateScheduler();

        $scheduler->schedulePeriodic(function () {
            $this->fail("This should never get called");
        }, 1, 1);
    }
}
