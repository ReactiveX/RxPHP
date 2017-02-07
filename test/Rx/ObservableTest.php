<?php

declare(strict_types = 1);

namespace Rx;

use Rx\Observable\ConnectableObservable;
use Rx\Observable\EmptyObservable;
use Rx\Observable\RefCountObservable;
use Rx\Observable\ReturnObservable;
use Rx\Scheduler\ImmediateScheduler;
use Rx\Testing\TestScheduler;

class ObservableTest extends TestCase
{
    public function testJustIsAliasForOf()
    {
        $o = new class extends Observable {
            private static $ofCalled = false;

            public static function ofWasCalled()
            {
                return static::$ofCalled;
            }

            public static function of($value, SchedulerInterface $scheduler = null): ReturnObservable
            {
                static::$ofCalled = true;
                return new ReturnObservable(123, new ImmediateScheduler());
            }

            protected function _subscribe(ObserverInterface $observer): DisposableInterface
            {
            }
        };

        $this->assertFalse($o::ofWasCalled());

        $o->just(1);

        $this->assertTrue($o::ofWasCalled());
    }

    public function testEmptyObservableIsAliasForEmpty()
    {
        $o = new class extends Observable {
            private static $emptyCalled = false;

            public static function emptyWasCalled()
            {
                return static::$emptyCalled;
            }

            public static function empty(SchedulerInterface $scheduler = null): EmptyObservable
            {
                static::$emptyCalled = true;
                return new EmptyObservable(new TestScheduler());
            }

            protected function _subscribe(ObserverInterface $observer): DisposableInterface
            {
            }
        };

        $this->assertFalse($o::emptyWasCalled());

        $o->emptyObservable();

        $this->assertTrue($o::emptyWasCalled());
    }

    public function testShareCallsPublishRefCount()
    {
        $o        = $this->getMockBuilder(Observable::class)
            ->setMethods(['publish'])
            ->getMockForAbstractClass();
        $oPublish = $this->getMockBuilder(Observable::class)
            ->setMethods(['refCount'])
            ->getMockForAbstractClass();
        $connectable = $this->createMock(ConnectableObservable::class);

        $o
            ->expects($this->once())
            ->method('publish')
            ->willReturn($oPublish);

        $oPublish
            ->expects($this->once())
            ->method('refCount')
            ->willReturn(new RefCountObservable($connectable, Observable::empty()));

        $o->share();
    }

    public function testShareValueCallsPublishValueRefCount()
    {
        $o        = $this->getMockBuilder(Observable::class)
            ->setMethods(['publishValue'])
            ->getMockForAbstractClass();
        $oPublish = $this->getMockBuilder(Observable::class)
            ->setMethods(['refCount'])
            ->getMockForAbstractClass();
        $connectable = $this->createMock(ConnectableObservable::class);

        $o
            ->expects($this->once())
            ->method('publishValue')
            ->with($this->equalTo(1))
            ->willReturn($oPublish);

        $oPublish
            ->expects($this->once())
            ->method('refCount')
            ->willReturn(new RefCountObservable($connectable, Observable::empty()));

        $o->shareValue(1);
    }

    public function testShareReplayCallsReplayRefCount()
    {
        $o        = $this->getMockBuilder(Observable::class)
            ->setMethods(['replay'])
            ->getMockForAbstractClass();
        $oPublish = $this->getMockBuilder(Observable::class)
            ->setMethods(['refCount'])
            ->getMockForAbstractClass();
        $connectable = $this->createMock(ConnectableObservable::class);

        $o
            ->expects($this->once())
            ->method('replay')
            ->with(
                null,
                $this->equalTo(123),
                $this->equalTo(456),
                null
            )
            ->willReturn($oPublish);

        $oPublish
            ->expects($this->once())
            ->method('refCount')
            ->willReturn(new RefCountObservable($connectable, Observable::empty()));

        $o->shareReplay(123, 456);
    }

    public function testCatchErrorCallsCatch()
    {
        $o = $this->getMockBuilder(Observable::class)
            ->setMethods(['catch'])
            ->getMockForAbstractClass();

        $callable = function () {

        };

        $o
            ->expects($this->once())
            ->method('catch')
            ->with($this->callback(function ($value) use ($callable) {
                return $value === $callable;
            }));


        $o->catchError($callable);
    }

    public function testSwitchLatestCallsSwitch()
    {
        $o = $this->getMockBuilder(Observable::class)
            ->setMethods(['switch'])
            ->getMockForAbstractClass();

        $o
            ->expects($this->once())
            ->method('switch');


        $o->switchLatest();
    }
}
