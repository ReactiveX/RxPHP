<?php declare(strict_types = 1);

namespace Rx\Functional\Scheduler;

use Interop\Async\Loop;
use Rx\Functional\FunctionalTestCase;
use Rx\Observable;
use Rx\Observer\CallbackObserver;

class EventLoopSchedulerTest extends FunctionalTestCase
{
    public function testDisposeInsideFirstSchedulePeriodicAction()
    {
        $completed = false;
        $nextCount = 0;

        Observable::interval(50)
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

        Loop::get()->run();

        $this->assertTrue($completed);
        $this->assertEquals(1, $nextCount);
    }
}
