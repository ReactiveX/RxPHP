<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;

class SumTest extends FunctionalTestCase
{
    /**
     * Adapted from RxJS
     *
     * @test
     */
    public function sum_number_empty()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->sum();
        });

        $this->assertMessages([
            onNext(250, 0),
            onCompleted(250)
        ], $results->getMessages());
    }

    /**
     * Adapted from RxJS
     *
     * @test
     */
    public function sum_number_return()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->sum();
        });

        $this->assertMessages([
            onNext(250, 2),
            onCompleted(250)
        ], $results->getMessages());
    }

    /**
     * Adapted from RxJS
     *
     * @test
     */
    public function sum_number_some()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onNext(220, 3),
            onNext(230, 4),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->sum();
        });

        $this->assertMessages([
            onNext(250, 2 + 3 + 4),
            onCompleted(250)
        ], $results->getMessages());
    }

    /**
     * Adapted from RxJS
     *
     * @test
     */
    public function sum_number_throw()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onError(210, new \Exception())
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->sum();
        });

        $this->assertMessages([
            onError(210, new \Exception())
        ], $results->getMessages());
    }

    /**
     * Adapted from RxJS
     *
     * @test
     */
    public function sum_number_never()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->sum();
        });

        $this->assertMessages([], $results->getMessages());
    }
}
