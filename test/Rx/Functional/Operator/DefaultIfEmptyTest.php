<?php

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable\BaseObservable;
use Rx\Observable\EmptyObservable;
use Rx\Observable\ReturnObservable;

class DefaultIfEmptyTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function defaultIfEmpty_nonEmpty_1()
    {

        $xs = $this->createHotObservable([
            onNext(280, 42),
            onNext(360, 43),
            onCompleted(420)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->defaultIfEmpty(new EmptyObservable());
        });

        $this->assertMessages([
            onNext(280, 42),
            onNext(360, 43),
            onCompleted(420)
        ], $results->getMessages());

        $this->assertSubscriptions([subscribe(200, 420)], $xs->getSubscriptions());

    }

    /**
     * @test
     */
    public function defaultIfEmpty_nonEmpty_2()
    {

        $xs = $this->createHotObservable([
            onNext(280, 42),
            onNext(360, 43),
            onCompleted(420)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->defaultIfEmpty(new ReturnObservable(-1));
        });

        $this->assertMessages([
            onNext(280, 42),
            onNext(360, 43),
            onCompleted(420)
        ], $results->getMessages());

        $this->assertSubscriptions([subscribe(200, 420)], $xs->getSubscriptions());

    }

    /**
     * @test
     */
    public function defaultIfEmpty_empty_1()
    {

        $xs = $this->createHotObservable([
            onCompleted(420)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->defaultIfEmpty(new ReturnObservable(null));
        });

        $this->assertMessages([
            onNext(420, null),
            onCompleted(420)
        ], $results->getMessages());

        $this->assertSubscriptions([subscribe(200, 420)], $xs->getSubscriptions());

    }

    /**
     * @test
     */
    public function defaultIfEmpty_empty_2()
    {

        $xs = $this->createHotObservable([
            onCompleted(420)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->defaultIfEmpty(new ReturnObservable(-1));
        });

        $this->assertMessages([
            onNext(420, -1),
            onCompleted(420)
        ], $results->getMessages());

        $this->assertSubscriptions([subscribe(200, 420)], $xs->getSubscriptions());

    }
}