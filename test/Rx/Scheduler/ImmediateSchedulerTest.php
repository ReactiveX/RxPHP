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

        $this->assertTrue(abs(time() - $scheduler->now()) < 1, "time difference is less than or equal to 1");
    }
}