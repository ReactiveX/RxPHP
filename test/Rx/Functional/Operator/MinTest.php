<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;

class MinTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function min_number_empty(): void
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->min();
        });

        $this->assertMessages([
            onError(250, new \Exception())
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function min_number_Return(): void
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->min();
        });

        $this->assertMessages([
            onNext(250, 2),
            onCompleted(250)
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function min_number_Some(): void
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 3),
            onNext(220, 4),
            onNext(230, 2),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->min();
        });

        $this->assertMessages([
            onNext(250, 2),
            onCompleted(250)
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function min_number_throw(): void
    {
        $error = new \Exception();

        $xs = $this->createHotObservable([
            onNext(150, 1),
            onError(210, $error)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->min();
        });

        $this->assertMessages([
            onError(210, $error)
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function min_number_Never(): void
    {
        $xs = $this->createHotObservable([
            onNext(150, 1)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->min();
        });

        $this->assertMessages([], $results->getMessages());
    }

    /**
     * @test
     */
    public function min_comparer_empty(): void
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->min(function ($a, $b) {
                return $b <=> $a;
            });
        });

        $this->assertMessages([
            onError(250, new \Exception())
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function min_comparer_return(): void
    {
        $xs = $this->createHotObservable([
            onNext(150, 'z'),
            onNext(210, 'a'),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->min(function ($a, $b) {
                return $b <=> $a;
            });
        });

        $this->assertMessages([
            onNext(250, 'a'),
            onCompleted(250)
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function min_comparer_some(): void
    {
        $xs = $this->createHotObservable([
            onNext(150, 'z'),
            onNext(210, 'b'),
            onNext(220, 'c'),
            onNext(230, 'a'),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->min(function ($a, $b) {
                return $b <=> $a;
            });
        });

        $this->assertMessages([
            onNext(250, 'c'),
            onCompleted(250)
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function min_comparer_throw(): void
    {
        $error = new \Exception();

        $xs = $this->createHotObservable([
            onNext(150, 'z'),
            onError(210, $error)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->min(function ($a, $b) {
                return $b <=> $a;
            });
        });

        $this->assertMessages([
            onError(210, $error)
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function min_comparer_never(): void
    {
        $xs = $this->createHotObservable([
            onNext(150, 'z')
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->min(function ($a, $b) {
                return $b <=> $a;
            });
        });

        $this->assertMessages([], $results->getMessages());
    }

    /**
     * @test
     */
    public function min_comparer_throws(): void
    {
        $error = new \Exception();

        $xs = $this->createHotObservable([
            onNext(150, 'z'),
            onNext(210, 'b'),
            onNext(220, 'c'),
            onNext(230, 'a'),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs, $error) {
            return $xs->min(function () use ($error): void {
                throw $error;
            });
        });

        $this->assertMessages([
            onError(220, $error)
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function min_never_dispose(): void
    {
        $error = new \Exception();

        $xs = $this->createHotObservable([
            onNext(150, 'z'),
        ]);

        $results = $this->scheduler->startWithDispose(function () use ($xs, $error) {
            return $xs->min();
        }, 400);

        $this->assertMessages([], $results->getMessages());
        
        $this->assertSubscriptions([
            subscribe(200, 400)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function min_some_dispose(): void
    {
        $error = new \Exception();

        $xs = $this->createHotObservable([
            onNext(150, 'z'),
            onNext(210, 'b'),
            onNext(220, 'c'),
            onNext(230, 'a')
        ]);

        $results = $this->scheduler->startWithDispose(function () use ($xs, $error) {
            return $xs->min();
        }, 400);

        $this->assertMessages([], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 400)
        ], $xs->getSubscriptions());
    }
}
