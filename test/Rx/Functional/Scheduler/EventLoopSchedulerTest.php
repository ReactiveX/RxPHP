<?php

namespace Rx\Functional\Scheduler;

use React\EventLoop\Factory;
use Rx\Functional\FunctionalTestCase;
use Rx\Observable;
use Rx\Observer\CallbackObserver;
use Rx\Scheduler\EventLoopScheduler;

class EventLoopSchedulerTest extends FunctionalTestCase
{
    public function testDisposeInsideFirstSchedulePeriodicAction()
    {
        $loop = Factory::create();

        $scheduler = new EventLoopScheduler($loop);

        $completed = false;
        $nextCount = 0;

        Observable::interval(50, $scheduler)
            ->take(1)
            ->subscribe(new CallbackObserver(
                function ($x) use (&$nextCount) {
                    $nextCount++;
                },
                function ($err) {
                    throw $err;
                },
                function () use (&$completed) {
                    $completed = true;
                }
            ));

        $loop->run();

        $this->assertTrue($completed);
        $this->assertEquals(1, $nextCount);
    }
}