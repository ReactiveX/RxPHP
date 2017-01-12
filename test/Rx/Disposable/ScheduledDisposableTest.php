<?php

declare(strict_types = 1);


namespace Rx\Disposable;

use Rx\TestCase;
use Rx\Testing\TestScheduler;

class ScheduledDisposableTest extends TestCase
{
    /**
     * @test
     */
    public function it_disposes_the_scheduled_disposable()
    {
        $disposed1 = false;

        $d1 = new CallbackDisposable(function () use (&$disposed1) {
            $disposed1 = true;
        });

        $scheduler = new TestScheduler();

        $disposable = new ScheduledDisposable($scheduler, $d1);

        $this->assertFalse($disposed1);

        $disposable->dispose();

        $this->assertFalse($disposed1);

        $scheduler->start();

        $this->assertTrue($disposed1);
    }

    /**
     * @test
     */
    public function it_does_nothing_if_disposed_twice()
    {
        $disposed1 = 0;

        $d1 = new CallbackDisposable(function () use (&$disposed1) {
            $disposed1++;
        });

        $scheduler = new TestScheduler();

        $disposable = new ScheduledDisposable($scheduler, $d1);

        $this->assertEquals(0, $disposed1);

        $disposable->dispose();

        $this->assertEquals(0, $disposed1);

        $scheduler->start();

        $this->assertEquals(1, $disposed1);

        $disposable->dispose();

        $this->assertEquals(1, $disposed1);

        $scheduler->start();
    }
}