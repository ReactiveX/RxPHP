<?php

declare(strict_types = 1);

namespace Rx\Observable;

use Rx\DisposableInterface;
use Rx\Observable;
use Rx\Subject\Subject;
use Rx\TestCase;

class RefCountObservableTest extends TestCase
{
    public function testRefCountDisposableOnlyDisposesOnce()
    {
        $source = $this->createMock(ConnectableObservable::class);

        $innerDisp = $this->createMock(DisposableInterface::class);

        $source
            ->expects($this->once())
            ->method('subscribe')
            ->willReturn($innerDisp);

        $innerDisp
            ->expects($this->once())
            ->method('dispose');

        $observable = new RefCountObservable($source);

        $subscription = $observable->subscribe();

        $subscription->dispose();
        $subscription->dispose();


    }

    public function testDisposesWhenRefcountReachesZero()
    {

    }
}
