<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable;
use Rx\Timestamped;

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
            onNext(210, new Timestamped(210, 2)),
            onNext(230, new Timestamped(230, 3)),
            onNext(260, new Timestamped(260, 4)),
            onNext(300, new Timestamped(300, 5)),
            onNext(350, new Timestamped(350, 6)),
            onCompleted(400)
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function timestamp_empty()
    {
        $results = $this->scheduler->startWithCreate(function () {
            return Observable::empty($this->scheduler)->timestamp($this->scheduler);
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
            return Observable::error($error, $this->scheduler)->timestamp($this->scheduler);
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
            onNext(210, new Timestamped(210, 2)),
            onNext(230, new Timestamped(230, 3)),
            onNext(260, new Timestamped(260, 4))
        ], $results->getMessages());
    }
}
