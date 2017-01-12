<?php

declare(strict_types = 1);

namespace Rx\Functional\Promise;

use Exception;
use Rx\Functional\FunctionalTestCase;
use Rx\Observable;
use Rx\Observer\CallbackObserver;
use Rx\Promise\Promise;
use Rx\Testing\MockObserver;

class FromPromiseTest extends FunctionalTestCase
{
    /**
     * @test
     *
     */
    public function from_promise_success()
    {
        $p = new Promise(Observable::of(42));

        $source = Observable::fromPromise($p);

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
        $p = new Promise(Observable::error(new Exception('error')));

        $source = Observable::fromPromise($p);

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
    public function two_observables_one_delayed()
    {
        $p = new Promise(Observable::of(1));

        $o1 = Observable::fromPromise($p);
        $o2 = Observable::fromPromise($p)->delay(200, $this->scheduler);

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

    }
}
