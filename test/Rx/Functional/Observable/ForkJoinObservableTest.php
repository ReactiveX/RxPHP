<?php

declare(strict_types = 1);

namespace Rx\Functional\Observable;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable;

class ForkJoinObservableTest extends FunctionalTestCase
{

    /**
     * @test
     */
    public function forkjoin_joins_last_values()
    {
        $e0 = $this->createHotObservable([
            onNext(150, 'a'),
            onNext(210, 'b'),
            onNext(250, 'c'),
            onNext(350, 'd'),
            onCompleted(420)
        ]);

        $e1 = $this->createHotObservable([
            onNext(220, 'b'),
            onCompleted(230)
        ]);

        $e2 = $this->createHotObservable([
            onNext(150, 1),
            onNext(230, 2),
            onNext(260, 3),
            onCompleted(400)
        ]);

        $xs = Observable::forkJoin([$e0, $e1, $e2]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs;
        });

        $this->assertMessages([
            onNext(420, ['d', 'b', 3]),
            onCompleted(420),
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function forkjoin_allows_null()
    {
        $e0 = $this->createHotObservable([
            onNext(150, 'a'),
            onNext(210, 'b'),
            onNext(250, 'c'),
            onNext(350, null),
            onCompleted(420)
        ]);

        $e1 = $this->createHotObservable([
            onNext(220, 'b'),
            onCompleted(230)
        ]);

        $e2 = $this->createHotObservable([
            onNext(150, 1),
            onNext(230, 2),
            onNext(260, 3),
            onCompleted(400)
        ]);

        $xs = Observable::forkJoin([$e0, $e1, $e2]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs;
        });

        $this->assertMessages([
            onNext(420, [null, 'b', 3]),
            onCompleted(420),
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function forkjoin_joins_last_values_with_selector()
    {
        $e0 = $this->createHotObservable([
            onNext(150, 'a'),
            onNext(210, 'b'),
            onNext(250, 'c'),
            onNext(350, 'd'),
            onCompleted(420)
        ]);

        $e1 = $this->createHotObservable([
            onNext(220, 'b'),
            onCompleted(230)
        ]);

        $e2 = $this->createHotObservable([
            onNext(150, 1),
            onNext(230, 2),
            onNext(260, 3),
            onCompleted(400)
        ]);

        $xs = Observable::forkJoin([$e0, $e1, $e2], function ($a, $b, $c) {
            return implode('', [$a, $b, $c]);
        });

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs;
        });

        $this->assertMessages([
            onNext(420, 'db3'),
            onCompleted(420),
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function forkjoin_accepts_single_observable()
    {
        $e0 = $this->createHotObservable([
            onNext(150, 'a'),
            onNext(210, 'b'),
            onNext(250, 'c'),
            onNext(350, 'd'),
            onCompleted(420)
        ]);

        $xs = Observable::forkJoin([$e0]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs;
        });

        $this->assertMessages([
            onNext(420, ['d']),
            onCompleted(420),
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function forkjoin_accepts_single_observable_with_selector()
    {
        $e0 = $this->createHotObservable([
            onNext(150, 'a'),
            onNext(210, 'b'),
            onNext(250, 'c'),
            onNext(350, 'd'),
            onCompleted(420)
        ]);

        $xs = Observable::forkJoin([$e0], function ($a) {
            return $a . $a;
        });

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs;
        });

        $this->assertMessages([
            onNext(420, 'dd'),
            onCompleted(420),
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function forkjoin_wont_emit_with_empty_observable()
    {
        $e0 = $this->createHotObservable([
            onNext(150, 1),
            onNext(215, 2),
            onNext(225, 4),
            onCompleted(230)
        ]);

        $e1 = $this->createHotObservable([
            onCompleted(235)
        ]);

        $e2 = $this->createHotObservable([
            onNext(150, 1),
            onNext(230, 3),
            onNext(245, 5),
            onCompleted(270)
        ]);

        $xs = Observable::forkJoin([$e0, $e1, $e2]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs;
        });

        $this->assertMessages([
            onCompleted(235),
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function forkjoin_empty_empty()
    {
        $e0 = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(230),
        ]);

        $e1 = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(250)
        ]);

        $xs = Observable::forkJoin([$e0, $e1], function ($a, $b) {
            return $a + $b;
        });

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs;
        });

        $this->assertMessages([
            onCompleted(230),
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function forkjoin_none()
    {
        $xs = Observable::forkJoin();

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs;
        });

        $this->assertMessages([
            onCompleted(200),
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function forkjoin_empty_return()
    {
        $e0 = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(230),
        ]);

        $e1 = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onCompleted(250)
        ]);

        $xs = Observable::forkJoin([$e0, $e1], function ($a, $b) {
            return $a + $b;
        });

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs;
        });

        $this->assertMessages([
            onCompleted(230),
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function forkjoin_return_empty()
    {
        $e0 = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onCompleted(230),
        ]);

        $e1 = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(250)
        ]);

        $xs = Observable::forkJoin([$e0, $e1], function ($a, $b) {
            return $a + $b;
        });

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs;
        });

        $this->assertMessages([
            onCompleted(250),
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function forkjoin_return_return()
    {
        $e0 = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onCompleted(230),
        ]);

        $e1 = $this->createHotObservable([
            onNext(150, 1),
            onNext(220, 3),
            onCompleted(250)
        ]);

        $xs = Observable::forkJoin([$e0, $e1], function ($a, $b) {
            return $a + $b;
        });

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs;
        });

        $this->assertMessages([
            onNext(250, 3 + 2),
            onCompleted(250),
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function forkjoin_empty_throw()
    {
        $error = new \Exception();

        $e0 = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(230),
        ]);

        $e1 = $this->createHotObservable([
            onNext(150, 1),
            onError(210, $error),
            onCompleted(250)
        ]);

        $xs = Observable::forkJoin([$e0, $e1], function ($a, $b) {
            return $a + $b;
        });

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs;
        });

        $this->assertMessages([
            onError(210, $error),
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function forkjoin_throw_empty()
    {
        $error = new \Exception();

        $e0 = $this->createHotObservable([
            onNext(150, 1),
            onError(210, $error),
            onCompleted(230),
        ]);

        $e1 = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(250)
        ]);

        $xs = Observable::forkJoin([$e0, $e1], function ($a, $b) {
            return $a + $b;
        });

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs;
        });

        $this->assertMessages([
            onError(210, $error),
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function forkjoin_return_throw()
    {
        $error = new \Exception();

        $e0 = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onCompleted(230),
        ]);

        $e1 = $this->createHotObservable([
            onNext(150, 1),
            onError(220, $error),
            onCompleted(250)
        ]);

        $xs = Observable::forkJoin([$e0, $e1], function ($a, $b) {
            return $a + $b;
        });

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs;
        });

        $this->assertMessages([
            onError(220, $error),
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function forkjoin_throw_return()
    {
        $error = new \Exception();

        $e0 = $this->createHotObservable([
            onNext(150, 1),
            onError(220, $error),
            onCompleted(230),
        ]);

        $e1 = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onCompleted(250)
        ]);

        $xs = Observable::forkJoin([$e0, $e1], function ($a, $b) {
            return $a + $b;
        });

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs;
        });

        $this->assertMessages([
            onError(220, $error),
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function forkjoin_throw_inside_selector()
    {
        $error = new \Exception();

        $e0 = $this->createHotObservable([
            onNext(220, 1),
            onCompleted(230),
        ]);

        $xs = Observable::forkJoin([$e0], function () use ($error) {
            throw $error;
        });

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs;
        });

        $this->assertMessages([
            onError(230, $error),
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function forkjoin_disposed_after_emit()
    {
        $e0 = $this->createHotObservable([
            onNext(250, 1),
            onCompleted(350),
        ]);

        $e1 = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onCompleted(250)
        ]);

        $xs = Observable::forkJoin([$e0, $e1]);

        $results = $this->scheduler->startWithDispose(function () use ($xs) {
            return $xs;
        }, 300);

        $this->assertMessages([], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 300)
        ], $e0->getSubscriptions());

        $this->assertSubscriptions([
            subscribe(200, 300)
        ], $e1->getSubscriptions());
    }
}