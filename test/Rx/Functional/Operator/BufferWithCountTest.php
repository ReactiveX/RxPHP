<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;

class BufferWithCountTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function bufferWithCountpartialwindow()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onNext(220, 3),
            onNext(230, 4),
            onNext(240, 5),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->bufferWithCount(5);
        });

        $this->assertMessages([
            onNext(250, [2, 3, 4, 5]),
            onCompleted(250)
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function bufferWithCountfullwindows()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onNext(220, 3),
            onNext(230, 4),
            onNext(240, 5),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->bufferWithCount(2);
        });

        $this->assertMessages([
            onNext(220, [2, 3]),
            onNext(240, [4, 5]),
            onCompleted(250)
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function bufferWithCountfullandpartialwindows()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onNext(220, 3),
            onNext(230, 4),
            onNext(240, 5),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->bufferWithCount(3);
        });

        $this->assertMessages([
            onNext(230, [2, 3, 4]),
            onNext(250, [5]),
            onCompleted(250)
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function bufferWithCountError()
    {
        $error = new \Exception();

        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onNext(220, 3),
            onNext(230, 4),
            onNext(240, 5),
            onError(250, $error)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->bufferWithCount(5);
        });

        $this->assertMessages([
            onError(250, $error)
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function bufferWithCountskipless()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onNext(220, 3),
            onNext(230, 4),
            onNext(240, 5),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->bufferWithCount(3, 1);
        });

        $this->assertMessages([
            onNext(230, [2, 3, 4]),
            onNext(240, [3, 4, 5]),
            onNext(250, [4, 5]),
            onNext(250, [5]),
            onCompleted(250)
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function bufferWithCountskipmore()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onNext(220, 3),
            onNext(230, 4),
            onNext(240, 5),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->bufferWithCount(2, 3);
        });

        $this->assertMessages([
            onNext(220, [2, 3]),
            onNext(250, [5]),
            onCompleted(250)
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function bufferWithCountbasic()
    {
        $xs = $this->createHotObservable([
            onNext(100, 1),
            onNext(210, 2),
            onNext(240, 3),
            onNext(280, 4),
            onNext(320, 5),
            onNext(350, 6),
            onNext(380, 7),
            onNext(420, 8),
            onNext(470, 9),
            onCompleted(600)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->bufferWithCount(3, 2);
        });

        $this->assertMessages([
            onNext(280, [2, 3, 4]),
            onNext(350, [4, 5, 6]),
            onNext(420, [6, 7, 8]),
            onNext(600, [8, 9]),
            onCompleted(600)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 600)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function bufferWithCountdisposed()
    {
        $xs = $this->createHotObservable([
            onNext(100, 1),
            onNext(210, 2),
            onNext(240, 3),
            onNext(280, 4),
            onNext(320, 5),
            onNext(350, 6),
            onNext(380, 7),
            onNext(420, 8),
            onNext(470, 9),
            onCompleted(600)
        ]);

        $results = $this->scheduler->startWithDispose(function () use ($xs) {
            return $xs->bufferWithCount(3, 2);
        }, 370);

        $this->assertMessages([
            onNext(280, [2, 3, 4]),
            onNext(350, [4, 5, 6])
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 370)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function bufferWithCount_invalid_skip()
    {
        $xs = $this->createHotObservable([
            onNext(100, 1),
            onNext(210, 2),
            onCompleted(300)
        ]);

        $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->bufferWithCount(0, 1);
        });
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function bufferWithCount_invalid_count()
    {
        $xs = $this->createHotObservable([
            onNext(100, 1),
            onNext(210, 2),
            onCompleted(300)
        ]);

        $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->bufferWithCount(1, 0);
        });
    }
}
