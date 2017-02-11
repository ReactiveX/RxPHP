<?php

namespace Rx\Testing;

use Rx\TestCase;

class HotObservableTest extends TestCase
{
    public function testRemovingObserverThatNeverSubscribed()
    {
        $scheduler = new TestScheduler();

        $hotObservable = new HotObservable($scheduler, []);

        $observer = new MockObserver($scheduler);

        $this->assertFalse($hotObservable->removeObserver($observer));
    }
}