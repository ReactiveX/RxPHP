<?php

namespace Rx\Observable;

use Exception;
use Rx\Disposable\RefCountDisposable;
use Rx\Observer\CallbackObserver;
use Rx\TestCase;

class GroupedObservableTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_the_disposable_of_the_underlying_disposable()
    {
        $disposable = $this->getMock('Rx\DisposableInterface');
        $observable = new AnonymousObservable(function() use (&$disposable) {
            return $disposable;
        });

        $groupedObservable = new GroupedObservable('key', $observable);

        $this->assertEquals($disposable, $groupedObservable->subscribe(new CallbackObserver()));
    }

    /**
     * @test
     */
    public function it_returns_a_composite_disposable_with_the_given_merged_disposable()
    {
        $d1 = $this->getMock('Rx\DisposableInterface');
        $d2 = new RefCountDisposable($d1);

        $observable = new AnonymousObservable(function() use (&$disposable) {
            return $disposable;
        });

        $groupedObservable = new GroupedObservable('key', $observable, $d2);

        $disposable = $groupedObservable->subscribe(new CallbackObserver());
        $this->assertInstanceOf('Rx\Disposable\CompositeDisposable', $disposable);
    }
}
