<?php

declare(strict_types = 1);

namespace Rx\Functional\Promise;

use Exception;
use Rx\Functional\FunctionalTestCase;
use Rx\Observable;
use Rx\React\RejectedPromiseException;
use Rx\Testing\MockObserver;

class FromPromiseTest extends FunctionalTestCase
{
    /**
     * @test
     *
     */
    public function from_promise_success()
    {
        $p = \React\Promise\resolve(42);

        $source = Observable::fromPromise($p);

        $source->subscribe(
            function ($x) {
                $this->assertEquals(42, $x);
            },
            function ($error) {
                $this->assertFalse(true);
            },
            function () {
                $this->assertTrue(true);
            });
    }

    /**
     * @test
     *
     */
    public function from_promise_failure()
    {
        $p = \React\Promise\reject('error');

        $source = Observable::fromPromise($p);

        $source->subscribe(
            function ($x) {
                $this->assertFalse(true);
            },
            function (Exception $error) {
                $this->assertInstanceOf(RejectedPromiseException::class, $error);
            },
            function () {
                $this->assertFalse(true);
            });
    }

    /**
     * @test
     */
    public function two_observables_one_delayed()
    {
        $p = \React\Promise\resolve(1);

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
