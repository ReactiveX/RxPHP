<?php

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable\BaseObservable;
use Rx\Observable\EmptyObservable;
use Rx\Testing\TestScheduler;

class ScanTest extends FunctionalTestCase
{
    public function testScanSeedNever()
    {
        $seed = 42;

        $results = $this->scheduler->startWithCreate(function () use ($seed) {
            return BaseObservable::never()->scan(function ($acc, $x) use ($seed) {
                return $acc + $x;
            }, $seed);
        });

        $this->assertMessages([], $results->getMessages());
    }

    public function testScanSeedEmpty()
    {
        $seed = 42;

        $xs = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs, $seed) {
            return $xs->scan(function ($acc, $x) use ($seed) {
                return $acc + $x;
            }, $seed);
        });

        $this->assertMessages(
            [
                onNext(250, 42),
                onCompleted(250)
            ],
            $results->getMessages()
        );


    }

    public function testScanSeedReturn()
    {
        $seed = 42;

        $xs = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(220, 2),
                onCompleted(250)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($xs, $seed) {
            return $xs->scan(function ($acc, $x) {
                return $acc + $x;
            }, $seed);
        });

        $this->assertMessages(
            [
                onNext(220, $seed + 2),
                onCompleted(250)
            ],
            $results->getMessages()
        );
    }

    public function testScanSeedThrow()
    {
        $ex = new \Exception('ex');

        $seed = 42;

        $xs = $this->createHotObservable(
            [
                onNext(150, 1),
                onError(250, $ex)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($xs, $seed) {
            return $xs->scan(function ($acc, $x) use ($seed) {
                return $acc + $x;
            }, $seed);
        });

        $this->assertMessages(
            [
                onError(250, $ex)
            ],
            $results->getMessages()
        );
    }

    public function testScanSeedSomeData()

    {
        $seed = 1;

        $xs = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(210, 2),
                onNext(220, 3),
                onNext(230, 4),
                onNext(240, 5),
                onCompleted(250)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($xs, $seed) {
            return $xs->scan(function ($acc, $x) use ($seed) {
                return $acc + $x;
            }, $seed);
        });

        $this->assertMessages(
            [
                onNext(210, $seed + 2),
                onNext(220, $seed + 2 + 3),
                onNext(230, $seed + 2 + 3 + 4),
                onNext(240, $seed + 2 + 3 + 4 + 5),
                onCompleted(250)
            ],
            $results->getMessages()
        );
    }

    public function testScanNoSeedNever()
    {
        $results = $this->scheduler->startWithCreate(function () {
            return BaseObservable::never()->scan(function ($acc, $x) {
                return $acc + $x;
            });
        });

        $this->assertMessages(
            [],
            $results->getMessages()
        );
    }

    public function testScanNoSeedEmpty()
    {
        $xs = $this->createHotObservable(
            [
                onNext(150, 1),
                onCompleted(250)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->scan(function ($acc, $x) {
                return $acc + $x;
            });
        });

        $this->assertMessages(
            [
                onCompleted(250)
            ],
            $results->getMessages()
        );
    }

    public function testScanNoSeedReturn()
    {
        $xs = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(220, 2),
                onCompleted(250)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->scan(function ($acc, $x) {
                return $acc + $x;
            });
        });

        $this->assertMessages(
            [
                onNext(220, 2),
                onCompleted(250)
            ],
            $results->getMessages()
        );
    }

    public function testScanNoSeedThrow()
    {
        $ex = new \Exception('ex');

        $xs = $this->createHotObservable(
            [
                onNext(150, 1),
                onError(250, $ex)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->scan(function ($acc, $x) {
                return $acc + $x;
            });
        });

        $this->assertMessages(
            [
                onError(250, $ex)
            ],
            $results->getMessages()
        );
    }

    public function testScanNoSeedSomeData()
    {
        $xs = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(210, 2),
                onNext(220, 3),
                onNext(230, 4),
                onNext(240, 5),
                onCompleted(250)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->scan(function ($acc, $x) {
                return $acc + $x;
            });
        });

        $this->assertMessages(
            [
                onNext(210, 2),
                onNext(220, 2 + 3),
                onNext(230, 2 + 3 + 4),
                onNext(240, 2 + 3 + 4 + 5),
                onCompleted(250)
            ],
            $results->getMessages()
        );
    }
}
