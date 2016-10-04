<?php

namespace Rx\Observable;

use Rx\Observable;
use Rx\TestCase;

class ObservableTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_false_on_trying_to_remove_a_non_subscribed_observer()
    {
        $observable = new ConcreteObservable();

        $observer = $this->createMock('Rx\ObserverInterface');

        $this->assertFalse($observable->removeObserver($observer));
    }
}

class ConcreteObservable extends Observable
{

}
