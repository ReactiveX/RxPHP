<?php

declare(strict_types = 1);

namespace Rx\Observer;

use Exception;
use Rx\TestCase;
use Rx\Testing\TestScheduler;

class ScheduledObserverTest extends TestCase
{
    /**
     * @test
     */
    public function it_calls_the_given_callable_on_next()
    {
        $called    = false;
        $scheduler = new TestScheduler();
        $observer  = new CallbackObserver(function () use (&$called) {
            $called = true;
        });

        $scheduledObserver = new ScheduledObserver($scheduler, $observer);

        $scheduledObserver->onNext(42);

        $this->assertFalse($called);

        $scheduledObserver->ensureActive();

        $this->assertFalse($called);

        $scheduler->start();

        $this->assertTrue($called);
    }

    /**
     * @test
     */
    public function it_calls_the_given_callable_on_error()
    {
        $called    = false;
        $scheduler = new TestScheduler();
        $observer  = new CallbackObserver(
            function () {
            },
            function () use (&$called) {
                $called = true;
            });

        $scheduledObserver = new ScheduledObserver($scheduler, $observer);

        $scheduledObserver->onError(new Exception());

        $this->assertFalse($called);

        $scheduledObserver->ensureActive();

        $this->assertFalse($called);

        $scheduler->start();

        $this->assertTrue($called);
    }

    /**
     * @test
     */
    public function it_calls_the_given_callable_on_complete()
    {
        $called    = false;
        $scheduler = new TestScheduler();
        $observer  = new CallbackObserver(
            function () {
            },
            function () {
            },
            function () use (&$called) {
                $called = true;
            });

        $scheduledObserver = new ScheduledObserver($scheduler, $observer);

        $scheduledObserver->onCompleted();

        $this->assertFalse($called);

        $scheduledObserver->ensureActive();

        $this->assertFalse($called);

        $scheduler->start();

        $this->assertTrue($called);
    }


    /**
     * @test
     */
    public function it_does_not_call_on_next_after_an_error()
    {
        $called    = false;
        $scheduler = new TestScheduler();
        $observer  = new CallbackObserver(
            function () use (&$called) {
                $called = true;
            },
            function () {
            }
        );

        $scheduledObserver = new ScheduledObserver($scheduler, $observer);

        $scheduledObserver->onError(new Exception());

        $this->assertFalse($called);

        $scheduledObserver->ensureActive();

        $this->assertFalse($called);

        $scheduler->start();

        $this->assertFalse($called);

        $scheduledObserver->onNext(42);

        $scheduledObserver->ensureActive();

        $scheduler->start();

        $this->assertFalse($called);
    }

    /**
     * @test
     */
    public function it_does_not_call_on_completed_after_an_error()
    {
        $called    = false;
        $scheduler = new TestScheduler();
        $observer  = new CallbackObserver(
            function () {
            },
            function () {
            },
            function () use (&$called) {
                $called = true;
            }
        );

        $scheduledObserver = new ScheduledObserver($scheduler, $observer);

        $scheduledObserver->onError(new Exception());

        $this->assertFalse($called);

        $scheduledObserver->ensureActive();

        $this->assertFalse($called);

        $scheduler->start();

        $this->assertFalse($called);

        $scheduledObserver->onCompleted();

        $scheduledObserver->ensureActive();

        $scheduler->start();

        $this->assertFalse($called);
    }

    /**
     * @test
     */
    public function it_does_not_call_on_error_after_an_error()
    {
        $called    = false;
        $scheduler = new TestScheduler();
        $observer  = new CallbackObserver(
            function () {
            },
            function () use (&$called) {
                $called = true;
            },
            function () {
            }
        );

        $scheduledObserver = new ScheduledObserver($scheduler, $observer);

        $scheduledObserver->onError(new Exception());

        $this->assertFalse($called);

        $scheduledObserver->ensureActive();

        $this->assertFalse($called);

        $scheduler->start();

        $this->assertTrue($called);

        $called = false;

        $scheduledObserver->onError(new Exception());

        $scheduledObserver->ensureActive();

        $scheduler->start();

        $this->assertFalse($called);
    }

    /**
     * @test
     */
    public function it_does_not_call_on_next_after_completion()
    {
        $called    = false;
        $scheduler = new TestScheduler();
        $observer  = new CallbackObserver(
            function () use (&$called) {
                $called = true;
            },
            function () {
            },
            function () {
            }
        );

        $scheduledObserver = new ScheduledObserver($scheduler, $observer);

        $scheduledObserver->onCompleted();

        $this->assertFalse($called);

        $scheduledObserver->ensureActive();

        $this->assertFalse($called);

        $scheduler->start();

        $this->assertFalse($called);

        $called = false;

        $scheduledObserver->onNext(42);

        $scheduledObserver->ensureActive();

        $scheduler->start();

        $this->assertFalse($called);
    }

    /**
     * @test
     */
    public function it_does_not_call_on_completed_after_completion()
    {
        $called    = false;
        $scheduler = new TestScheduler();
        $observer  = new CallbackObserver(
            function () {
            },
            function () {
            },
            function () use (&$called) {
                $called = true;
            }
        );

        $scheduledObserver = new ScheduledObserver($scheduler, $observer);

        $scheduledObserver->onCompleted();

        $this->assertFalse($called);

        $scheduledObserver->ensureActive();

        $this->assertFalse($called);

        $scheduler->start();

        $this->assertTrue($called);

        $called = false;

        $scheduledObserver->onCompleted();

        $scheduledObserver->ensureActive();

        $scheduler->start();

        $this->assertFalse($called);
    }

    /**
     * @test
     */
    public function it_does_not_call_on_error_after_completion()
    {
        $called    = false;
        $scheduler = new TestScheduler();
        $observer  = new CallbackObserver(
            function () {
            },
            function () use (&$called) {
                $called = true;
            },
            function () {
            }
        );

        $scheduledObserver = new ScheduledObserver($scheduler, $observer);

        $scheduledObserver->onCompleted();

        $this->assertFalse($called);

        $scheduledObserver->ensureActive();

        $this->assertFalse($called);

        $scheduler->start();

        $this->assertFalse($called);

        $called = false;

        $scheduledObserver->onError(new Exception());

        $scheduledObserver->ensureActive();

        $scheduler->start();

        $this->assertFalse($called);
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage onNext(0) exception
     */
    public function throw_inside_onnext_throws()
    {
        $scheduler = new TestScheduler();

        $scheduledObserver = new ScheduledObserver(
            $scheduler,
            new CallbackObserver(
                function ($x) {
                    throw new Exception("onNext($x) exception");
                }
            )
        );

        $scheduledObserver->onNext(0);

        $scheduledObserver->ensureActive();

        $scheduler->start();
    }
}

