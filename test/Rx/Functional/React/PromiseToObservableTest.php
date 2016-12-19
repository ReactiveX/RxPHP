<?php


namespace Rx\Functional\React;

use Exception;
use React\Promise\Deferred;
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
    public function from_promise_success()
    {
        $p = Promise::resolved(42);

        $source = Promise::toObservable($p);

        $source->subscribe(new CallbackObserver(
          function ($x) {
              $this->assertEquals(42, $x);
          },
          function ($error) {
              $this->assertFalse(true);
          },
          function () {
              $this->assertTrue(true);
          }));
    }

    /**
     * @test
     *
     */
    public function from_promise_failure()
    {
        $p = Promise::rejected(new Exception('error'));

        $source = Promise::toObservable($p);

        $source->subscribe(new CallbackObserver(
          function ($x) {
              $this->assertFalse(true);

          },
          function ($error) {
              $this->assertEquals($error, new Exception('error'));
          },
          function () {
              $this->assertFalse(true);
          }));

    }

    /**
     * @test
     */
    public function to_observable_cancels_on_dispose()
    {
        $canceled = false;

        $deferred = new Deferred(function () use (&$canceled) {
            $canceled = true;
        });

        $o = Promise::toObservable($deferred->promise());

        $this->scheduler->schedule(function () use ($deferred) {
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
    public function two_observables_one_delayed()
    {
        $canceled = false;
        
        $deferred = new Deferred(function () use (&$canceled) {
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
    public function two_observables_one_disposed_before_resolve()
    {
        $canceled = false;

        $deferred = new Deferred(function () use (&$canceled) {
            $canceled = true;
        });

        $o1 = Promise::toObservable($deferred->promise());
        $o2 = Promise::toObservable($deferred->promise())->delay(100, $this->scheduler);

        $this->scheduler->schedule(function () use ($deferred) {
            $deferred->resolve(1);
        }, 100);
        

        $results1 = new MockObserver($this->scheduler);

        $s1 = $o1->subscribe($results1);
        
        $this->scheduler->schedule(function () use ($s1) {
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
    public function observable_dispose_after_complete()
    {
        $canceled = false;

        $deferred = new Deferred(function () use (&$canceled) {
            $canceled = true;
        });

        $o = Promise::toObservable($deferred->promise());
        
        $this->scheduler->schedule(function () use ($deferred) {
            $deferred->resolve(1);
        }, 200);
        
        $results = new MockObserver($this->scheduler);

        $s = $o->subscribe($results);

        $this->scheduler->schedule(function () use ($s) {
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
