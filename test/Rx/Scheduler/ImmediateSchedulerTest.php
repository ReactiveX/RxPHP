<?php


namespace Rx\Scheduler;


use Rx\TestCase;

class ImmediateSchedulerTest extends TestCase
{
    /**
     * @test
     */
    public function now_returns_the_time() {
        $scheduler = new ImmediateScheduler();

        $this->assertTrue(abs(time() * 1000 - $scheduler->now()) < 1000, "time difference is less than or equal to 1");
    }
}