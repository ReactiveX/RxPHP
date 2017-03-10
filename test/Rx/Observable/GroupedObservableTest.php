<?php

declare(strict_types = 1);

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
        $disposable = $this->createMock('Rx\DisposableInterface');
        
        $disposable->expects($this->once())
            ->method('dispose');
        
        $observable = new AnonymousObservable(function() use (&$disposable) {
            return $disposable;
        });

        $groupedObservable = new GroupedObservable('key', $observable);

        $groupedObservable->subscribe(new CallbackObserver())->dispose();
    }

    /**
     * @test
     */
    public function it_exposes_its_key()
    {
        $observable = new AnonymousObservable(function(){});

        $groupedObservable = new GroupedObservable('key', $observable);
        $this->assertEquals('key', $groupedObservable->getKey());
    }
}
