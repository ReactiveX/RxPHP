<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use \Exception;

class TakeLastTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function takeLast_zero_completed()
    {
        $xs = $this->createHotObservable([
            onNext(180, 1),
            onNext(210, 2),
            onNext(250, 3),
            onNext(270, 4),
            onNext(310, 5),
            onNext(360, 6),
            onNext(380, 7),
            onNext(410, 8),
            onNext(590, 9),
            onCompleted(650)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->takeLast(0);
        });

        $this->assertMessages([
            onCompleted(650)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 650)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function takeLast_zero_error()
    {

        $error = new Exception('error');

        $xs = $this->createHotObservable([
            onNext(180, 1),
            onNext(210, 2),
            onNext(250, 3),
            onNext(270, 4),
            onNext(310, 5),
            onNext(360, 6),
            onNext(380, 7),
            onNext(410, 8),
            onNext(590, 9),
            onError(650, $error)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->takeLast(0);
        });

        $this->assertMessages([
            onError(650, $error)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 650)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function takeLast_zero_disposed()
    {
        $xs = $this->createHotObservable([
            onNext(180, 1),
            onNext(210, 2),
            onNext(250, 3),
            onNext(270, 4),
            onNext(310, 5),
            onNext(360, 6),
            onNext(380, 7),
            onNext(410, 8),
            onNext(590, 9)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->takeLast(0);
        });

        $this->assertMessages([], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 1000)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function takeLast_one_completed()
    {
        $xs = $this->createHotObservable([
            onNext(180, 1),
            onNext(210, 2),
            onNext(250, 3),
            onNext(270, 4),
            onNext(310, 5),
            onNext(360, 6),
            onNext(380, 7),
            onNext(410, 8),
            onNext(590, 9),
            onCompleted(650)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->takeLast(1);
        });

        $this->assertMessages([
            onNext(650, 9),
            onCompleted(650)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 650)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function takeLast_one_error()
    {

        $error = new Exception('error');

        $xs = $this->createHotObservable([
            onNext(180, 1),
            onNext(210, 2),
            onNext(250, 3),
            onNext(270, 4),
            onNext(310, 5),
            onNext(360, 6),
            onNext(380, 7),
            onNext(410, 8),
            onNext(590, 9),
            onError(650, $error)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->takeLast(1);
        });

        $this->assertMessages([
            onError(650, $error)
        ], $results->getMessages());


        $this->assertSubscriptions([
            subscribe(200, 650)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function takeLast_one_disposed()
    {
        $xs = $this->createHotObservable([
            onNext(180, 1),
            onNext(210, 2),
            onNext(250, 3),
            onNext(270, 4),
            onNext(310, 5),
            onNext(360, 6),
            onNext(380, 7),
            onNext(410, 8),
            onNext(590, 9)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->takeLast(1);
        });

        $this->assertMessages([], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 1000)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function takeLast_three_completed()
    {
        $xs = $this->createHotObservable([
            onNext(180, 1),
            onNext(210, 2),
            onNext(250, 3),
            onNext(270, 4),
            onNext(310, 5),
            onNext(360, 6),
            onNext(380, 7),
            onNext(410, 8),
            onNext(590, 9),
            onCompleted(650)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->takeLast(3);
        });

        $this->assertMessages([
            onNext(650, 7),
            onNext(650, 8),
            onNext(650, 9),
            onCompleted(650)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 650)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function takeLast_three_error()
    {

        $error = new Exception('error');

        $xs = $this->createHotObservable([
            onNext(180, 1),
            onNext(210, 2),
            onNext(250, 3),
            onNext(270, 4),
            onNext(310, 5),
            onNext(360, 6),
            onNext(380, 7),
            onNext(410, 8),
            onNext(590, 9),
            onError(650, $error)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->takeLast(3);
        });

        $this->assertMessages([
            onError(650, $error)
        ], $results->getMessages());


        $this->assertSubscriptions([
            subscribe(200, 650)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function takeLast_three_disposed()
    {
        $xs = $this->createHotObservable([
            onNext(180, 1),
            onNext(210, 2),
            onNext(250, 3),
            onNext(270, 4),
            onNext(310, 5),
            onNext(360, 6),
            onNext(380, 7),
            onNext(410, 8),
            onNext(590, 9)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->takeLast(3);
        });

        $this->assertMessages([], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 1000)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function takeLast_invalid_count()
    {
        $xs = $this->createHotObservable([
            onNext(180, 1),
            onNext(210, 2),
            onCompleted(250)
        ]);

        $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->takeLast(-1);
        });
    }
}
