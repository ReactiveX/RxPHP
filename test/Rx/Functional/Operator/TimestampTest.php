<?php

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable;

class TimestampTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function timestamp_regular()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onNext(230, 3),
            onNext(260, 4),
            onNext(300, 5),
            onNext(350, 6),
            onCompleted(400)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->timestamp($this->scheduler);
        });

        $this->assertMessages([
            onNext(210, (object)["value" => 2, "timestamp" => 210]),
            onNext(230, (object)["value" => 3, "timestamp" => 230]),
            onNext(260, (object)["value" => 4, "timestamp" => 260]),
            onNext(300, (object)["value" => 5, "timestamp" => 300]),
            onNext(350, (object)["value" => 6, "timestamp" => 350]),
            onCompleted(400)
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function timestamp_empty()
    {
        $results = $this->scheduler->startWithCreate(function () {
            return Observable::emptyObservable($this->scheduler)->timestamp($this->scheduler);
        });

        $this->assertMessages([
            onCompleted(201)
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function timestamp_error()
    {
        $error = new \Exception();

        $results = $this->scheduler->startWithCreate(function () use ($error) {
            return Observable::error($error)->timestamp($this->scheduler);
        });

        $this->assertMessages([
            onError(201, $error)
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function timestamp_never()
    {
        $results = $this->scheduler->startWithCreate(function () {
            return Observable::never()->timestamp($this->scheduler);
        });

        $this->assertMessages([], $results->getMessages());
    }

    /**
     * @test
     */
    public function timestamp_dispose()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onNext(230, 3),
            onNext(260, 4),
            onNext(300, 5),
            onNext(350, 6),
            onCompleted(400)
        ]);

        $results = $this->scheduler->startWithDispose(function () use ($xs) {
            return $xs->timestamp($this->scheduler);
        }, 275);

        $this->assertMessages([
            onNext(210, (object)["value" => 2, "timestamp" => 210]),
            onNext(230, (object)["value" => 3, "timestamp" => 230]),
            onNext(260, (object)["value" => 4, "timestamp" => 260]),
        ], $results->getMessages());
    }
}
