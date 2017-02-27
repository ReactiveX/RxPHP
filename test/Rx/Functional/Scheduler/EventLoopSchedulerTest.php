<?php

declare(strict_types = 1);

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
        $completed = false;
        $nextCount = 0;

        $loop = Factory::create();

        Observable::interval(50, new EventLoopScheduler($loop))
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
