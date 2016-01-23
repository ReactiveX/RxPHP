<?php

namespace Rx\Scheduler;

use React\EventLoop\Factory;
use Rx\Observable;
use Rx\TestCase;

class EventLoopSchedulerTest extends TestCase
{
    /**
     * @test
     */
    public function now_returns_the_time()
    {
        $loop      = Factory::create();
        $scheduler = new EventLoopScheduler($loop);

        $this->assertTrue(abs(time() - $scheduler->now()) < 1, "time difference is less than or equal to 1");
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

        $action = function ($reschedule) use (&$actionCalled, &$count) {
            $actionCalled = true;
            $count++;
            if ($count < 5) {
                $reschedule();
            }
        };

        $disposable = $scheduler->scheduleRecursive($action);

        $this->assertInstanceOf('Rx\DisposableInterface', $disposable);
        $this->assertFalse($actionCalled);
        $this->assertEquals(0, $count);

        $loop->tick();
        $this->assertEquals(1, $count);

        $loop->tick();
        $this->assertEquals(2, $count);

        $loop->tick();
        $this->assertEquals(3, $count);

        $loop->tick();
        $this->assertEquals(4, $count);

        $loop->tick();
        $this->assertEquals(5, $count);

        $loop->tick();
        $this->assertTrue($actionCalled);
        $this->assertEquals(5, $count);

    }
}
