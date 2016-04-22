<?php

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
                return Observable::timer(5)->mapTo($x);
            })->concatAll();
        });

        $this->assertMessages([
            onNext(206, 0),
            onNext(211, 1),
            onNext(216, 2),
            onCompleted(216)
        ], $results->getMessages());
    }
}