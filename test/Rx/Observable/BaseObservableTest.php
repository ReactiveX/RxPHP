<?php

namespace Rx\Observable;

use Rx\TestCase;
use Rx\Observable\BaseObservable;

class BaseObservableTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_false_on_trying_to_remove_a_non_subscribed_observer()
    {
        $observable = new ConcreteObservable();

        $observer = $this->getMock('Rx\ObserverInterface');

        $this->assertFalse($observable->removeObserver($observer));
    }
}

class ConcreteObservable extends BaseObservable
{
    public function doStart($scheduler)
    {
        // meh
    }
}
