<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;


use Rx\Functional\FunctionalTestCase;
use Rx\Observable\ReturnObservable;
use Rx\Observer\CallbackObserver;


class TakeTest extends FunctionalTestCase
{
    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function it_throws_an_exception_on_negative_amounts()
    {
        $observable = new ReturnObservable(42, $this->scheduler);
        $result     = $observable->take(-1);

        $result->subscribe(new CallbackObserver());
    }

    /**
     * @test
     */
    public function it_passes_on_complete()
    {
        $xs = $this->createHotObservable([
            onNext(300, 21),
            onNext(500, 42),
            onNext(800, 84),
            onCompleted(820),
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->take(5);
        });

        $this->assertMessages([
            onNext(300, 21),
            onNext(500, 42),
            onNext(800, 84),
            onCompleted(820),
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function it_calls_on_complete_after_last_value()
    {
        $scheduler = $this->createTestScheduler();
        $xs        = $this->createHotObservable([
            onNext(300, 21),
            onNext(500, 42),
            onNext(800, 84),
            onCompleted(820),
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->take(2);
        });

        $this->assertMessages([
            onNext(300, 21),
            onNext(500, 42),
            onCompleted(500),
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function take_zero_calls_on_completed()
    {

        $xs = $this->createHotObservable([
            onNext(300, 21),
            onNext(500, 42),
            onNext(800, 84),
            onCompleted(820),
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->take(0);
        });

        $this->assertMessages([
            onCompleted(200),
        ], $results->getMessages());
    }
}
