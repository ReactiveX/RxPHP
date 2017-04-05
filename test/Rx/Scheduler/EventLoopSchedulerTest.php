<?php

namespace Rx\Scheduler;

use React\EventLoop\Factory;
use Rx\Observable;
use Rx\Disposable\CallbackDisposable;
use Rx\TestCase;

class EventLoopSchedulerTest extends TestCase
{
    /**
     * @test
     */
    public function now_returns_time_since_epoch_in_ms()
    {
        $loop      = Factory::create();
        $scheduler = new EventLoopScheduler($loop);

        $this->assertTrue(abs(time() * 1000 - $scheduler->now()) < 1000, "time difference is less than or equal to 1");
    }

    /**
     * @test
     */
    public function eventloop_schedule()
    {

        $loop         = Factory::create();
        $scheduler    = new EventLoopScheduler($loop);
        $actionCalled = false;

        $action = function () use (&$actionCalled) {
            $actionCalled = true;
            return "test";
        };

        $disposable = $scheduler->schedule($action);

        $this->assertInstanceOf('Rx\DisposableInterface', $disposable);
        $this->assertFalse($actionCalled);

        $loop->tick();

        $this->assertTrue($actionCalled);

    }

    /**
     * @test
     */
    public function eventloop_schedule_recursive()
    {

        $loop         = Factory::create();
        $scheduler    = new EventLoopScheduler($loop);
        $actionCalled = false;
        $count        = 0;

        $action = function ($reschedule) use (&$actionCalled, &$count, $loop) {
            $actionCalled = true;
            $count++;
            if ($count < 5) {
                $reschedule();
                return;
            }
            $loop->stop();
        };

        $disposable = $scheduler->scheduleRecursive($action);

        $this->assertInstanceOf('Rx\DisposableInterface', $disposable);
        $this->assertFalse($actionCalled);
        $this->assertEquals(0, $count);

        $loop->run();
        
        $this->assertEquals(5, $count);
        $this->assertTrue($actionCalled);
    }
    
    public function testDisposedEventDoesNotCauseSkip()
    {
        // create a scheduler - timing is not important for this test
        // so we can just use an empty callable
        $scheduler = new EventLoopScheduler(function () {
        });
        
        $calls = [];
        
        // the way that these are scheduled, if the scheduler runs (by calling start a few times),
        // calls should be [2] because 0 is disposed and 1 shouldn't be called for 10s
        $disposable = $scheduler->schedule(function () use (&$calls) {
            $calls[] = 0;
        }, 0);

        $scheduler->schedule(function () use (&$calls) {
            $calls[] = 1;
        }, 10000);

        $scheduler->schedule(function () use (&$calls) {
            $calls[] = 2;
        }, 0);
        
        $disposable->dispose();
        
        $scheduler->start();
        $scheduler->start();
        $scheduler->start();
        
        $this->assertEquals([2], $calls);
    }

    public function testSchedulerWorkedWithScheduledEventOutsideItself()
    {
        $loop         = Factory::create();
        $scheduler    = new EventLoopScheduler($loop);

        $scheduler->start();
        $start = microtime(true);
        $called = null;

        $loop->addTimer(0.1, function () use ($scheduler, &$called) {
            $scheduler->schedule(function () use (&$called) {
                $called = microtime(true);
            }, 100);
        });

        $loop->run();

        $this->assertEquals(0.2, $called-$start, '', 0.02);
    }

    public function testScheduledItemsFromOutsideOfSchedulerDontCreateExtraTimers()
    {
        $timersCreated   = 0;
        $timersExecuted = 0;
        $loop           = Factory::create();
        $scheduler      = new EventLoopScheduler(function ($delay, $action) use ($loop, &$timersCreated, &$timersExecuted) {
            $timersCreated++;
            $timer = $loop->addTimer($delay * 0.001, function () use ($action, &$timersExecuted) {
                $timersExecuted++;
                $action();
            });
            return new CallbackDisposable(function () use ($timer) {
                $timer->cancel();
            });
        });

        $scheduler->schedule(function () {}, 20);

        $scheduler->schedule(function () {}, 15)->dispose();
        $scheduler->schedule(function () {}, 14)->dispose();
        $scheduler->schedule(function () {}, 13)->dispose();
        $scheduler->schedule(function () {}, 12)->dispose();

        $scheduler->schedule(function () {}, 10);

        $loop->run();

        $this->assertEquals($timersCreated, 3);
        $this->assertEquals($timersExecuted, 3);
    }

    public function testMultipleSchedulersFromOutsideInSameTickDontCreateExtraTimers()
    {
        $timersCreated   = 0;
        $timersExecuted = 0;
        $loop           = Factory::create();
        $scheduler      = new EventLoopScheduler(function ($delay, $action) use ($loop, &$timersCreated, &$timersExecuted) {
            $timersCreated++;
            $timer = $loop->addTimer($delay * 0.001, function () use ($action, &$timersExecuted) {
                $timersExecuted++;
                $action();
            });
            return new CallbackDisposable(function () use ($timer) {
                $timer->cancel();
            });
        });

        $scheduler->schedule(function () {}, 20);
        $loop->addTimer(0.01, function () use ($scheduler) {
            $scheduler->schedule(function () {}, 30);

            $scheduler->schedule(function () {}, 25)->dispose();
            $scheduler->schedule(function () {}, 24)->dispose();
            $scheduler->schedule(function () {}, 23)->dispose();
            $scheduler->schedule(function () {}, 22)->dispose();
        });

        $loop->run();

        $this->assertEquals($timersCreated, 3);
        $this->assertEquals($timersExecuted, 3);
    }

    public function testThatStuffScheduledWayInTheFutureDoesntKeepTheLoopRunningIfDisposed()
    {
        $loop           = Factory::create();
        $scheduler      = new EventLoopScheduler(function ($delay, $action) use ($loop, &$timersCreated, &$timersExecuted) {
            $timersCreated++;
            $timer = $loop->addTimer($delay * 0.001, function () use ($action, &$timersExecuted) {
                $timersExecuted++;
                $action();
            });
            return new CallbackDisposable(function () use ($timer) {
                $timer->cancel();
            });
        });

        $disp = $scheduler->schedule(function () {}, 3000);
        $loop->addTimer(0.01, function () use ($scheduler, $disp) {
            $scheduler->schedule(function () use ($disp) {
                $disp->dispose();
            });
        });

        $beforeLoopStart = microtime(true);
        $loop->run();
        $loopTime = microtime(true) - $beforeLoopStart;

        $this->assertLessThan(2, $loopTime);
    }
}
