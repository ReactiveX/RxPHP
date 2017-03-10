<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable;

class ScanTest extends FunctionalTestCase
{
    public function testScanSeedNever()
    {
        $seed = 42;

        $results = $this->scheduler->startWithCreate(function () use ($seed) {
            return Observable::never()->scan(function ($acc, $x) use ($seed) {
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
            return Observable::never()->scan(function ($acc, $x) {
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


    /**
     * @test
     */
    public function scan_accumulator_throws()
    {
        $xs = $this->createHotObservable(
          [
            onNext(150, 1),
            onNext(210, 2),
            onNext(230, 3),
            onCompleted(240)
          ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->scan(function () {
                throw new \Exception();
            });
        });

        $this->assertMessages([onNext(210, 2), onError(230, new \Exception())], $results->getMessages());
    }

    /**
     * @test
     */
    public function scan_accumulator_throws_with_seed()
    {
        $xs = $this->createHotObservable(
          [
            onNext(150, 1),
            onNext(210, 2),
            onNext(230, 3),
            onCompleted(240)
          ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->scan(function () {
                throw new \Exception();
            }, 42);
        });

        $this->assertMessages([onError(210, new \Exception())], $results->getMessages());
    }

}
