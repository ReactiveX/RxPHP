<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable;
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
            return $xs->defaultIfEmpty(new EmptyObservable($this->scheduler));
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
            return $xs->defaultIfEmpty(new ReturnObservable(-1, $this->scheduler));
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
            return $xs->defaultIfEmpty(new ReturnObservable(null, $this->scheduler));
        });

        // Note: these tests differ from the RxJS tests that they were based on because RxJS was
        // explicitly using the immediate scheduler on subscribe internally. When we pass the
        // proper scheduler in, the subscription gets scheduled which requires an extra tick.

        $this->assertMessages([
            onNext(421, null),
            onCompleted(421)
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
            return $xs->defaultIfEmpty(new ReturnObservable(-1, $this->scheduler));
        });

        // Note: these tests differ from the RxJS tests that they were based on because RxJS was
        // explicitly using the immediate scheduler on subscribe internally. When we pass the
        // proper scheduler in, the subscription gets scheduled which requires an extra tick.
        $this->assertMessages([
            onNext(421, -1),
            onCompleted(421)
        ], $results->getMessages());

        $this->assertSubscriptions([subscribe(200, 420)], $xs->getSubscriptions());

    }
}