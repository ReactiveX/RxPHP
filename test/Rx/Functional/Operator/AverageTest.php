<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;

class AverageTest extends FunctionalTestCase
{
    /**
     * Adapted from RxJS
     *
     * @test
     */
    public function average_Number_Empty(): void
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->average();
        });

        $this->assertMessages([
            onError(250, new \UnderflowException())
        ], $results->getMessages());
    }
    
    /**
     * Adapted from RxJS
     *
     * @test
     */
    public function average_Number_Return(): void
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->average();
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
    public function average_Number_Some(): void
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 3),
            onNext(220, 4),
            onNext(230, 2),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->average();
        });

        $this->assertMessages([
            onNext(250, 3),
            onCompleted(250)
        ], $results->getMessages());
    }
    /**
     * Adapted from RxJS
     *
     * @test
     */
    public function average_Number_Throw(): void
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onError(210, new \Exception())
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->average();
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
    public function average_Number_Never(): void
    {
        $xs = $this->createHotObservable([
            onNext(150, 1)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->average();
        });

        $this->assertMessages([], $results->getMessages());

        $this->assertSubscriptions([subscribe(200, 1000)], $xs->getSubscriptions());
    }
}
