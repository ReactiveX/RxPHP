<?php

declare(strict_types = 1);

namespace Rx\Scheduler;

use React\EventLoop\Factory;
use Rx\TestCase;

class EventLoopSchedulerTest extends TestCase
{
    /**
     * @test
     */
    public function now_returns_time_since_epoch_in_ms()
    {
        $scheduler = new EventLoopScheduler(function () {});

        $this->assertTrue(abs(time() * 1000 - $scheduler->now()) < 1000, 'time difference is less than or equal to 1');
    }

    /**
     * @test
     */
    public function eventloop_schedule()
    {
        $loop = Factory::create();

        $scheduler    = new EventLoopScheduler($loop);
        $actionCalled = false;

        $action = function () use (&$actionCalled) {
            $actionCalled = true;
            return 'test';
        };

        $disposable = $scheduler->schedule($action);

        $this->assertInstanceOf('Rx\DisposableInterface', $disposable);
        $this->assertFalse($actionCalled);

        $loop->futureTick(function () use ($loop) {
            $loop->stop();
        });

        $loop->run();

        $this->assertTrue($actionCalled);

    }

    /**
     * @test
     */
    public function eventloop_schedule_recursive()
    {

        $loop = Factory::create();
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
        $loop      = Factory::create();
        $scheduler = new EventLoopScheduler($loop);

        $scheduler->start();
        $called = null;

        $loop->addTimer(0.100, function () use ($scheduler, &$called) {
            $scheduler->schedule(function () use (&$called) {
                $called = microtime(true);
            }, 100);
        });

        $loop->run();

        $this->assertNotNull($called);
    }
}
