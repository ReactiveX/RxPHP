<?php

declare(strict_types = 1);

namespace Rx;

use Rx\Scheduler\EventLoopScheduler;
use Rx\Scheduler\ImmediateScheduler;
use Rx\Testing\TestScheduler;

class SchedulerTest extends TestCase
{
    private function resetStaticScheduler()
    {
        $ref = new \ReflectionClass(Scheduler::class);
        foreach (['default', 'async', 'immediate', 'defaultFactory', 'asyncFactory'] as $propertyName) {
            $prop = $ref->getProperty($propertyName);
            $prop->setAccessible(true);
            $prop->setValue(null);
            $prop->setAccessible(false);
        }
    }

    public function setup()
    {
        $this->resetStaticScheduler();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Please set a default scheduler factory
     */
    public function testGetDefaultThrowsIfNotSet()
    {
        $scheduler = Scheduler::getDefault();

        $this->assertInstanceOf(EventLoopScheduler::class, $scheduler);
    }

    public function testSetDefault()
    {
        $scheduler = new TestScheduler();

        Scheduler::setDefaultFactory(function () use ($scheduler) {
            return $scheduler;
        });

        $this->assertSame($scheduler, Scheduler::getDefault());
    }

    public function testSetDefaultTwiceBeforeGet()
    {
        $scheduler = new TestScheduler();

        Scheduler::setDefaultFactory(function () use ($scheduler) {
            return $scheduler;
        });

        $scheduler2 = new TestScheduler();

        Scheduler::setDefaultFactory(function () use ($scheduler2) {
            return $scheduler2;
        });

        $this->assertSame($scheduler2, Scheduler::getDefault());
    }

    public function testSetAsync()
    {
        $scheduler = new TestScheduler();

        Scheduler::setAsyncFactory(function () use ($scheduler) {
            return $scheduler;
        });

        $this->assertSame($scheduler, Scheduler::getAsync());
    }

    public function testSetAsyncTwiceBeforeGet()
    {
        $scheduler = new TestScheduler();

        Scheduler::setAsyncFactory(function () use ($scheduler) {
            return $scheduler;
        });

        $scheduler2 = new TestScheduler();

        Scheduler::setAsyncFactory(function () use ($scheduler2) {
            return $scheduler2;
        });

        $this->assertSame($scheduler2, Scheduler::getAsync());
    }

    /**
     * @expectedException \Exception
     */
    public function testSetDefaultTwiceThrowsException()
    {
        $scheduler = new TestScheduler();

        Scheduler::setDefaultFactory(function () use ($scheduler) {
            return $scheduler;
        });

        Scheduler::getDefault();

        Scheduler::setDefaultFactory(function () use ($scheduler) {
            return $scheduler;
        });
    }


    /**
     * @expectedException \Exception
     */
    public function testSetAsyncTwiceThrowsException()
    {
        $scheduler = new TestScheduler();

        Scheduler::setAsyncFactory(function () use ($scheduler) {
            return $scheduler;
        });

        Scheduler::getAsync();

        Scheduler::setAsyncFactory(function () use ($scheduler) {
            return $scheduler;
        });
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Please set a default scheduler factory
     */
    public function testGetAsyncBeforeSet()
    {
        Scheduler::getAsync();
    }

    public function testGetAsyncAfterSettingDefaultToAsync()
    {
        $asyncScheduler = new EventLoopScheduler(function () {
        });

        Scheduler::setDefaultFactory(function () use ($asyncScheduler) {
            return $asyncScheduler;
        });

        $this->assertSame($asyncScheduler, Scheduler::getAsync());
    }

    /**
     * @expectedException \Throwable
     * @expectedExceptionMessage Return value of Rx\Scheduler::getAsync() must implement interface Rx\AsyncSchedulerInterface, instance of Rx\Scheduler\ImmediateScheduler returned
     */
    public function testAsyncSchedulerFactorReturnsNonAsyncScheduler()
    {
        Scheduler::setAsyncFactory(function () {
            return new ImmediateScheduler();
        });

        Scheduler::getAsync();
    }

    /**
     * @expectedException \Throwable
     * @expectedExceptionMessage Return value of Rx\Scheduler::getDefault() must implement interface Rx\SchedulerInterface, instance of stdClass returned
     */
    public function testDefaultSchedulerFactorReturnsNonScheduler()
    {
        Scheduler::setDefaultFactory(function () {
            return new \stdClass();
        });

        Scheduler::getDefault();
    }

    /**
     * @expectedException \Throwable
     * @expectedExceptionMessage Please set an async scheduler factory
     */
    public function testAsyncSchedulerFactorThrowsNonAsyncDefaultScheduler()
    {
        Scheduler::setDefaultFactory(function () {
            return new ImmediateScheduler();
        });

        Scheduler::getAsync();
    }
}
