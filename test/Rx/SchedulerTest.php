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

    public function setup() : void
    {
        $this->resetStaticScheduler();
    }

    /**
     */
    public function testGetDefaultThrowsIfNotSet()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Please set a default scheduler factory');
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
     */
    public function testSetDefaultTwiceThrowsException()
    {
        $this->expectException(\Exception::class);
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
     */
    public function testSetAsyncTwiceThrowsException()
    {
        $this->expectException(\Exception::class);
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
     */
    public function testGetAsyncBeforeSet()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Please set a default scheduler factory');

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
     */
    public function testAsyncSchedulerFactorReturnsNonAsyncScheduler()
    {
        $this->expectException(\Throwable::class);
        if (phpversion() < '8.0.0') {
            $this->expectExceptionMessage('Return value of Rx\Scheduler::getAsync() must implement interface Rx\AsyncSchedulerInterface, instance of Rx\Scheduler\ImmediateScheduler returned');
        } else {
            $this->expectExceptionMessage('Rx\Scheduler::getAsync(): Return value must be of type Rx\AsyncSchedulerInterface, Rx\Scheduler\ImmediateScheduler returned');
        }

        Scheduler::setAsyncFactory(function () {
            return new ImmediateScheduler();
        });

        Scheduler::getAsync();
    }

    /**
     */
    public function testDefaultSchedulerFactorReturnsNonScheduler()
    {
        $this->expectException(\Throwable::class);
        if (phpversion() < '8.0.0') {
            $this->expectExceptionMessage('Return value of Rx\Scheduler::getDefault() must implement interface Rx\SchedulerInterface, instance of stdClass returned');
        } else {
            $this->expectExceptionMessage('Rx\Scheduler::getDefault(): Return value must be of type Rx\SchedulerInterface, stdClass returned');
        }
        Scheduler::setDefaultFactory(function () {
            return new \stdClass();
        });

        Scheduler::getDefault();
    }

    /**
     */
    public function testAsyncSchedulerFactorThrowsNonAsyncDefaultScheduler()
    {
        $this->expectException(\Throwable::class);
        $this->expectExceptionMessage('Please set an async scheduler factory');

        Scheduler::setDefaultFactory(function () {
            return new ImmediateScheduler();
        });

        Scheduler::getAsync();
    }
}
