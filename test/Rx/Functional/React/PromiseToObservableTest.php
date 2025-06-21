<?php


namespace Rx\Functional\React;

use React\Promise\Deferred;
use React\Promise\Promise as ReactPromise;
use Rx\Functional\FunctionalTestCase;
use Rx\Observer\CallbackObserver;
use Rx\React\Promise;
use Rx\Testing\MockObserver;

class PromiseToObservableTest extends FunctionalTestCase
{
    /**
     * @test
     *
     */
    public function from_promise_success(): void
    {
        $p = Promise::resolved(42);

        $source = Promise::toObservable($p);

        $source->subscribe(new CallbackObserver(
          function ($x): void {
              $this->assertEquals(42, $x);
          },
          function ($error): void {
              $this->assertFalse(true);
          },
          function (): void {
              $this->assertTrue(true);
          }));
    }

    /**
     * @test
     *
     */
    public function from_promise_failure(): void
    {
        $p = new ReactPromise(function (): void {
            1 / 0;
        });

        $source = Promise::toObservable($p);

        $source->subscribe(new CallbackObserver(
          function ($x): void {
              $this->assertFalse(true);

          },
          function (\Throwable $error): void {
              $this->assertStringContainsStringIgnoringCase('division by zero', $error->getMessage());
          },
          function (): void {
              $this->assertFalse(true);
          }));

    }

    /**
     * @test
     */
    public function to_observable_cancels_on_dispose(): void
    {
        $canceled = false;

        $deferred = new Deferred(function () use (&$canceled): void {
            $canceled = true;
        });

        $o = Promise::toObservable($deferred->promise());

        $this->scheduler->schedule(function () use ($deferred): void {
            $deferred->resolve(1);
        }, 300);
        
        $results = $this->scheduler->startWithDispose(function () use ($o) {
            // adding the merge causes 2 subscriptions to test that the count
            // of cancels works correctly through disposal
            return $o->merge($o);
        }, 250);
        
        $this->assertMessages([
            
        ], $results->getMessages());
        
        $this->assertTrue($canceled);
    }
    
    /**
     * @test
     */
    public function two_observables_one_delayed(): void
    {
        $canceled = false;
        
        $deferred = new Deferred(function () use (&$canceled): void {
            $canceled = true;
        });
        
        $o1 = Promise::toObservable($deferred->promise());
        $o2 = Promise::toObservable($deferred->promise())->delay(200, $this->scheduler);
        
        $deferred->resolve(1);
        
        $results1 = new MockObserver($this->scheduler);
        
        $o1->subscribe($results1);
        
        $results2 = new MockObserver($this->scheduler);
        $o2->subscribe($results2);

        $this->scheduler->start();
        
        $this->assertMessages([
            onNext(0, 1),
            onCompleted(0)
        ], $results1->getMessages());

        $this->assertMessages([
            onNext(200, 1),
            onCompleted(200)
        ], $results2->getMessages());
        
        $this->assertFalse($canceled);
    }

    /**
     * @test
     */
    public function two_observables_one_disposed_before_resolve(): void
    {
        $canceled = false;

        $deferred = new Deferred(function () use (&$canceled): void {
            $canceled = true;
        });

        $o1 = Promise::toObservable($deferred->promise());
        $o2 = Promise::toObservable($deferred->promise())->delay(100, $this->scheduler);

        $this->scheduler->schedule(function () use ($deferred): void {
            $deferred->resolve(1);
        }, 100);
        

        $results1 = new MockObserver($this->scheduler);

        $s1 = $o1->subscribe($results1);
        
        $this->scheduler->schedule(function () use ($s1): void {
            $s1->dispose();
        }, 50);

        $results2 = new MockObserver($this->scheduler);
        $o2->subscribe($results2);

        $this->scheduler->start();

        $this->assertMessages([
        ], $results1->getMessages());

        $this->assertMessages([
            onNext(200, 1),
            onCompleted(200)
        ], $results2->getMessages());
        
        $this->assertFalse($canceled);
    }
    
    /**
     * @test
     */
    public function observable_dispose_after_complete(): void
    {
        $canceled = false;

        $deferred = new Deferred(function () use (&$canceled): void {
            $canceled = true;
        });

        $o = Promise::toObservable($deferred->promise());
        
        $this->scheduler->schedule(function () use ($deferred): void {
            $deferred->resolve(1);
        }, 200);
        
        $results = new MockObserver($this->scheduler);

        $s = $o->subscribe($results);

        $this->scheduler->schedule(function () use ($s): void {
            $s->dispose();
        }, 250);
        
        $this->scheduler->start();

        $this->assertMessages([
            onNext(200, 1),
            onCompleted(200)
        ], $results->getMessages());
        
        $this->assertFalse($canceled);
    }
}
