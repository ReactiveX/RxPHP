<?php

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

class FinallyCallTest extends FunctionalTestCase
{

    /**
     * @test
     */
    public function should_call_finally_after_complete()
    {
        $completed = false;

        Observable::fromArray([1, 2, 3])
            ->finally(function() use (&$completed) {
                $completed = true;
            })
            ->subscribe(new CallbackObserver());

        $this->assertTrue($completed);
    }

    /**
     * @test
     */
    public function should_call_finally_after_error()
    {
        $thrown = false;

        Observable::fromArray([1, 2, 3])
            ->map(function($value) {
                if ($value == 3) {
                    throw new \Exception();
                }
                return $value;
            })
            ->finally(function() use (&$thrown) {
                $thrown = true;
            })
            ->subscribe(new CallbackObserver(null, function() {})); // Ignore the default error handler

        $this->assertTrue($thrown);
    }

    /**
     * @test
     */
    public function should_call_finally_upon_disposal()
    {
        $disposed = false;

        Observable::create(function(ObserverInterface $obs) {
                $obs->onNext(1);
            })
            ->finally(function() use (&$disposed) {
                $disposed = true;
            })
            ->subscribe(new CallbackObserver())
            ->dispose();

        $this->assertTrue($disposed);
    }

    /**
     * @test
     */
    public function should_call_finally_when_synchronously_subscribing_to_and_unsubscribing_from_a_shared_observable()
    {
        $disposed = false;

        Observable::create(function(ObserverInterface $obs) {
                $obs->onNext(1);
            })
            ->finally(function() use (&$disposed) {
                $disposed = true;
            })
            ->share()
            ->subscribe(new CallbackObserver())
            ->dispose();

        $this->assertTrue($disposed);
    }

    /**
     * @test
     */
    public function should_call_two_finally_instances_in_succession_on_a_shared_observable()
    {
        $invoked = 0;

        Observable::fromArray([1, 2, 3])
            ->finally(function() use (&$invoked) {
                $invoked++;
            })
            ->finally(function() use (&$invoked) {
                $invoked++;
            })
            ->share()
            ->subscribe(new CallbackObserver());

        $this->assertEquals(2, $invoked);
    }

    /**
     * @test
     */
    public function should_handle_empty()
    {
        $executed = false;

        $xs = $this->createHotObservable([
            onCompleted(300)
        ]);

        $results = $this->scheduler->startWithCreate(function() use ($xs, &$executed) {
            return $xs->finally(function() use (&$executed) {
                $executed = true;
            });
        });

        $this->assertTrue($executed);

        $this->assertMessages([
            onCompleted(300),
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 300),
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function should_handle_never()
    {
        $executed = false;

        $xs = $this->createHotObservable([ ]);

        $results = $this->scheduler->startWithCreate(function() use ($xs, &$executed) {
            return $xs->finally(function() use (&$executed) {
                $executed = true;
            });
        });

        $this->assertTrue($executed);

        $this->assertMessages([ ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 1000),
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function should_handle_throw()
    {
        $executed = false;

        $e = new \Exception();

        $xs = $this->createHotObservable([
            onError(300, $e)
        ]);

        $results = $this->scheduler->startWithCreate(function() use ($xs, &$executed) {
            return $xs->finally(function() use (&$executed) {
                $executed = true;
            });
        });

        $this->assertTrue($executed);

        $this->assertMessages([
            onError(300, $e)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 300),
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function should_handle_basic_hot_observable()
    {
        $executed = false;

        $xs = $this->createHotObservable([
            onNext(300, 'a'),
            onNext(400, 'b'),
            onNext(500, 'c'),
            onCompleted(600),
        ]);

        $results = $this->scheduler->startWithCreate(function() use ($xs, &$executed) {
            return $xs->finally(function() use (&$executed) {
                $executed = true;
            });
        });

        $this->assertTrue($executed);

        $this->assertMessages([
            onNext(300, 'a'),
            onNext(400, 'b'),
            onNext(500, 'c'),
            onCompleted(600),
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 600),
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function should_handle_basic_cold_observable()
    {
        $executed = false;

        $xs = $this->createColdObservable([
            onNext(300, 'a'),
            onNext(400, 'b'),
            onNext(500, 'c'),
            onCompleted(600),
        ]);

        $results = $this->scheduler->startWithCreate(function() use ($xs, &$executed) {
            return $xs->finally(function() use (&$executed) {
                $executed = true;
            });
        });

        $this->assertTrue($executed);

        $this->assertMessages([
            onNext(500, 'a'),
            onNext(600, 'b'),
            onNext(700, 'c'),
            onCompleted(800),
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 800),
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function should_handle_basic_error()
    {
        $executed = false;

        $e = new \Exception();

        $xs = $this->createHotObservable([
            onNext(300, 'a'),
            onNext(400, 'b'),
            onNext(500, 'c'),
            onError(600, $e),
        ]);

        $results = $this->scheduler->startWithCreate(function() use ($xs, &$executed) {
            return $xs->finally(function() use (&$executed) {
                $executed = true;
            });
        });

        $this->assertTrue($executed);

        $this->assertMessages([
            onNext(300, 'a'),
            onNext(400, 'b'),
            onNext(500, 'c'),
            onError(600, $e),
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 600),
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function should_handle_unsubscription()
    {
        $executed = false;

        $xs = $this->createHotObservable([
            onNext(300, 'a'),
            onNext(400, 'b'),
            onNext(500, 'c'),
        ]);

        $results = $this->scheduler->startWithDispose(function() use ($xs, &$executed) {
            return $xs->finally(function() use (&$executed) {
                $executed = true;
            });
        }, 450);

        $this->assertTrue($executed);

        $this->assertMessages([
            onNext(300, 'a'),
            onNext(400, 'b'),
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 450),
        ], $xs->getSubscriptions());
    }

}