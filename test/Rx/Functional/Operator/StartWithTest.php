<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable;

class StartWithTest extends FunctionalTestCase
{

    /**
     * @test
     */
    public function startWith_never()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->startWith(1, $this->scheduler);
        });

        $this->assertMessages([
            onNext(201, 1)
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function startWith_empty()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->startWith(1, $this->scheduler);
        });

        $this->assertMessages([
            onNext(201, 1),
            onCompleted(250)
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function startWith_one()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(220, 2),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->startWith(1, $this->scheduler);
        });

        $this->assertMessages([
            onNext(201, 1),
            onNext(220, 2),
            onCompleted(250)
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function startWith_multiple()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(220, 4),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->startWithArray([1, 2, 3], $this->scheduler);
        });

        $this->assertMessages([
            onNext(201, 1),
            onNext(202, 2),
            onNext(203, 3),
            onNext(220, 4),
            onCompleted(250)
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function startWith_multiple_before()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(202, 4),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->startWithArray([1, 2, 3], $this->scheduler);
        });

        $this->assertMessages([
            onNext(201, 1),
            onNext(202, 2),
            onNext(203, 3),
            onCompleted(250)
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function startWith_error()
    {

        $error = new \Exception();

        $xs = $this->createHotObservable([
            onNext(150, 1),
            onError(250, $error)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->startWithArray([1, 2, 3], $this->scheduler);
        });

        $this->assertMessages([
            onNext(201, 1),
            onNext(202, 2),
            onNext(203, 3),
            onError(250, $error)
        ], $results->getMessages());
    }
}
