<?php

declare(strict_types = 1);

namespace Rx\Scheduler;

use React\EventLoop\Factory;
use Rx\Disposable\CallbackDisposable;
use Rx\Disposable\EmptyDisposable;
use Rx\TestCase;

class EventLoopSchedulerTest extends TestCase
{
    /**
     * @test
     */
    public function now_returns_time_since_epoch_in_ms(): void
    {
        $scheduler = new EventLoopScheduler(function () { return new EmptyDisposable(); });

        $this->assertTrue(abs(time() * 1000 - $scheduler->now()) < 1000, 'time difference is less than or equal to 1');
    }

    /**
     * @test
     */
    public function eventloop_schedule(): void
    {
        $loop = Factory::create();

        $scheduler    = new EventLoopScheduler($loop);
        $actionCalled = false;

        $action = function () use (&$actionCalled) {
            $actionCalled = true;
            return 'test';
        };

        $disposable = $scheduler->schedule($action);

        $this->assertInstanceOf(\Rx\DisposableInterface::class, $disposable);
        $this->assertFalse($actionCalled);

        $loop->futureTick(function () use ($loop): void {
            $loop->stop();
        });

        $loop->run();

        $this->assertTrue($actionCalled);

    }

    /**
     * @test
     */
    public function eventloop_schedule_recursive(): void
    {

        $loop = Factory::create();
        $scheduler    = new EventLoopScheduler($loop);
        $actionCalled = false;
        $count        = 0;

        $action = function ($reschedule) use (&$actionCalled, &$count, $loop): void {
            $actionCalled = true;
            $count++;
            if ($count < 5) {
                $reschedule();
                return;
            }
            $loop->stop();
        };

        $disposable = $scheduler->scheduleRecursive($action);

        $this->assertInstanceOf(\Rx\DisposableInterface::class, $disposable);
        $this->assertFalse($actionCalled);
        $this->assertEquals(0, $count);

        $loop->run();

        $this->assertEquals(5, $count);
        $this->assertTrue($actionCalled);
    }

    public function testDisposedEventDoesNotCauseSkip(): void
    {
        // create a scheduler - timing is not important for this test
        // so we can just use an empty callable
        $scheduler = new EventLoopScheduler(function () {
            return new EmptyDisposable();
        });

        $calls = [];

        // the way that these are scheduled, if the scheduler runs (by calling start a few times),
        // calls should be [2] because 0 is disposed and 1 shouldn't be called for 10s
        $disposable = $scheduler->schedule(function () use (&$calls): void {
            $calls[] = 0;
        }, 0);

        $scheduler->schedule(function () use (&$calls): void {
            $calls[] = 1;
        }, 10000);

        $scheduler->schedule(function () use (&$calls): void {
            $calls[] = 2;
        }, 0);

        $disposable->dispose();

        $scheduler->start();
        $scheduler->start();
        $scheduler->start();

        $this->assertEquals([2], $calls);
    }

    public function testSchedulerWorkedWithScheduledEventOutsideItself(): void
    {
        $loop      = Factory::create();
        $scheduler = new EventLoopScheduler($loop);

        $scheduler->start();
        $called = null;

        $loop->addTimer(0.100, function () use ($scheduler, &$called): void {
            $scheduler->schedule(function () use (&$called): void {
                $called = microtime(true);
            }, 100);
        });

        $loop->run();

        $this->assertNotNull($called);
    }

    public function testScheduledItemsFromOutsideOfSchedulerDontCreateExtraTimers(): void
    {
        $timersCreated   = 0;
        $timersExecuted = 0;
        $loop           = Factory::create();
        $scheduler      = new EventLoopScheduler(function ($delay, $action) use ($loop, &$timersCreated, &$timersExecuted) {
            $timersCreated++;
            $timer = $loop->addTimer($delay * 0.001, function () use ($action, &$timersExecuted): void {
                $timersExecuted++;
                $action();
            });
            return new CallbackDisposable(function () use ($loop, $timer): void {
                $loop->cancelTimer($timer);
            });
        });

        $scheduler->schedule(function (): void {}, 40);

        $scheduler->schedule(function (): void {}, 35)->dispose();
        $scheduler->schedule(function (): void {}, 34)->dispose();

        $scheduler->schedule(function (): void {}, 20);

        $loop->run();

        $this->assertLessThanOrEqual(3, $timersCreated);
        $this->assertLessThanOrEqual(3, $timersExecuted);
    }

    public function testMultipleSchedulesFromOutsideInSameTickDontCreateExtraTimers(): void
    {
        $timersCreated   = 0;
        $timersExecuted = 0;
        $loop           = Factory::create();
        $scheduler      = new EventLoopScheduler(function ($delay, $action) use ($loop, &$timersCreated, &$timersExecuted) {
            $timersCreated++;
            $timer = $loop->addTimer($delay * 0.001, function () use ($action, &$timersExecuted): void {
                $timersExecuted++;
                $action();
            });
            return new CallbackDisposable(function () use ($loop, $timer): void {
                $loop->cancelTimer($timer);
            });
        });

        $scheduler->schedule(function (): void {}, 20);
        $loop->addTimer(0.01, function () use ($scheduler): void {
            $scheduler->schedule(function (): void {}, 30);

            $scheduler->schedule(function (): void {}, 25)->dispose();
            $scheduler->schedule(function (): void {}, 24)->dispose();
            $scheduler->schedule(function (): void {}, 23)->dispose();
            $scheduler->schedule(function (): void {}, 25)->dispose();
        });

        $loop->run();

        $this->assertEquals(3, $timersCreated);
        $this->assertEquals(3, $timersExecuted);
    }

    public function testThatStuffScheduledWayInTheFutureDoesntKeepTheLoopRunningIfDisposed(): void
    {
        $loop           = Factory::create();
        $scheduler      = new EventLoopScheduler(function ($delay, $action) use ($loop, &$timersCreated, &$timersExecuted) {
            $timersCreated++;
            $timer = $loop->addTimer($delay * 0.001, function () use ($action, &$timersExecuted): void {
                $timersExecuted++;
                $action();
            });
            return new CallbackDisposable(function () use ($loop, $timer): void {
                $loop->cancelTimer($timer);
            });
        });

        $disp = $scheduler->schedule(function (): void {}, 3000);
        $loop->addTimer(0.01, function () use ($scheduler, $disp): void {
            $scheduler->schedule(function () use ($disp): void {
                $disp->dispose();
            });
        });

        $beforeLoopStart = microtime(true);
        $loop->run();
        $loopTime = microtime(true) - $beforeLoopStart;

        $this->assertLessThan(2, $loopTime);
    }

    public function testThatDisposalOfSingleScheduledItemOutsideOfInvokeCancelsTimer(): void
    {
        $loop      = Factory::create();
        $scheduler = new EventLoopScheduler($loop);

        $startTime = microtime(true);

        $disp = $scheduler->schedule(function (): void {}, 3000);
        $loop->addTimer(0.01, function () use ($disp): void {
            $disp->dispose();
        });

        $loop->run();
        $endTime = microtime(true);

        $this->assertLessThan(2, $endTime - $startTime);
    }

    public function testScheduledItemPastNextScheduledItemKillsItOwnTimerIfItBecomesTheNextOneAndIsDisposed(): void
    {
        $loop      = Factory::create();
        $scheduler = new EventLoopScheduler($loop);

        $startTime = microtime(true);

        $scheduler->schedule(function (): void {}, 30);
        $disp  = $scheduler->schedule(function (): void {}, 3000);
        $loop->addTimer(0.050, function () use ($disp): void {
            $disp->dispose();
        });

        $loop->run();
        $endTime = microtime(true);

        $this->assertLessThan(2, $endTime - $startTime);
    }
}
