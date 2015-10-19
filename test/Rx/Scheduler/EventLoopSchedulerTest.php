<?php


namespace Rx\Scheduler;


use React\EventLoop\Factory;
use Rx\TestCase;

class EventLoopSchedulerTest extends TestCase
{
    /**
     * @test
     */
    public function now_returns_the_time() {
        $loop = Factory::create();
        $scheduler = new EventLoopScheduler($loop);

        $this->assertTrue(abs(time() - $scheduler->now()) < 1, "time difference is less than or equal to 1");
    }
}