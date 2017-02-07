<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable;

class ConcatAllTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function concatAll_timer_missing_item()
    {
        $xs = $this->createHotObservable([
            onNext(201, 0),
            onNext(206, 1),
            onNext(211, 2),
            onCompleted(212)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->map(function ($x) {
                return Observable::timer(5, $this->scheduler)->mapTo($x);
            })->concatAll();
        });

        $this->assertMessages([
            onNext(206, 0),
            onNext(211, 1),
            onNext(216, 2),
            onCompleted(216)
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function concatAll_errors_when_exception_during_inner_subscribe()
    {
        $o1 = Observable::create(function () {
            throw new \Exception("Exception in inner subscribe");
        });

        $xs = $this->createHotObservable([
            onNext(300, $o1),
            onCompleted(400)
        ]);

        $result = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->concatAll();
        });

        $this->assertMessages([
            onError(300, new \Exception())
        ], $result->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 300)
        ], $xs->getSubscriptions());
    }
}