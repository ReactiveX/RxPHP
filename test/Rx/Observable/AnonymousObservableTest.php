<?php

declare(strict_types = 1);

namespace Rx\Observable;

use Rx\TestCase;
use Rx\Disposable\CallbackDisposable;
use Rx\Disposable\EmptyDisposable;

class AnonymousObservableTest extends TestCase
{

    /**
     * @test
     */
    public function it_calls_the_subscribe_action_on_subscribe(): void
    {
        $called = 0;
        $observable = new AnonymousObservable(function() use (&$called) { $called++; return new EmptyDisposable(); });

        $observerMock = $this->createMock(\Rx\ObserverInterface::class);
        $observable->subscribe($observerMock);

        $this->assertEquals(1, $called);
    }

    /**
     * @test
     */
    public function the_returned_disposable_disposes(): void
    {
        $disposed = false;

        $observable = new AnonymousObservable(function() use (&$disposed) {
            return new CallbackDisposable(function() use (&$disposed): void {
                $disposed = true;
            });
        });

        $observerMock = $this->createMock(\Rx\ObserverInterface::class);
        $disposable = $observable->subscribe($observerMock);

        $disposable->dispose();

        $this->assertTrue($disposed);
    }

    /**
     * @test
     */
    public function it_throws_when_args_invalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $observable = new AnonymousObservable(function (): void {
        });

        $observable->subscribe('invalid arg');
    }
}
