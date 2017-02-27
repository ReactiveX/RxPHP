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
        foreach (['default', 'async', 'immediate'] as $propertyName) {
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
     * @expectedExceptionMessage Please set a default scheduler (for react: Scheduler::setDefault(new EventLoopScheduler($loop));
     */
    public function testGetDefaultThrowsIfNotSet()
    {
        $scheduler = Scheduler::getDefault();

        $this->assertInstanceOf(EventLoopScheduler::class, $scheduler);
    }

    public function testSetDefault()
    {
        $scheduler = new TestScheduler();

        Scheduler::setDefault($scheduler);

        $this->assertSame($scheduler, Scheduler::getDefault());
    }

    public function testSetAsync()
    {
        $scheduler = new TestScheduler();

        Scheduler::setAsync($scheduler);

        $this->assertSame($scheduler, Scheduler::getAsync());
    }

    public function testSetImmediate()
    {
        $scheduler = new ImmediateScheduler();

        Scheduler::setImmediate($scheduler);

        $this->assertSame($scheduler, Scheduler::getImmediate());
    }

    /**
     * @expectedException \Exception
     */
    public function testSetDefaultAfterDefaultStartThrowsException()
    {
        $scheduler = new TestScheduler();

        Scheduler::setDefault($scheduler);

        $scheduler->start();

        $scheduler2 = new TestScheduler();
        
        Scheduler::setDefault($scheduler2);
    }

    /**
     * @expectedException \Exception
     */
    public function testSetAsyncTwiceThrowsException()
    {
        $scheduler = new TestScheduler();

        Scheduler::setAsync($scheduler);

        $scheduler2 = new TestScheduler();

        Scheduler::setAsync($scheduler2);
    }

    /**
     * @expectedException \Exception
     */
    public function testSetImmediateTwiceThrowsException()
    {
        $scheduler = new ImmediateScheduler();

        Scheduler::setImmediate($scheduler);

        $scheduler2 = new ImmediateScheduler();

        Scheduler::setImmediate($scheduler2);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Please set an async scheduler (for react: Scheduler::setAsync(new EventLoopScheduler($loop));
     */
    public function testGetAsyncBeforeSet()
    {
        Scheduler::getAsync();
    }

    public function testGetAsyncAfterSettingDefaultToAsync()
    {
        $asyncScheduler = new EventLoopScheduler(function () {});

        Scheduler::setDefault($asyncScheduler);

        $this->assertSame($asyncScheduler, Scheduler::getAsync());
    }
}
