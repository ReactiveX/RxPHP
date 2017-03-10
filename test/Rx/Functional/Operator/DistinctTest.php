<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable;

class DistinctTest extends FunctionalTestCase
{

    /**
     * @test
     */
    public function distinct_default_comparer_all_distinct()
    {
        $xs = $this->createHotObservable([
            onNext(280, 4),
            onNext(300, 2),
            onNext(350, 1),
            onNext(380, 3),
            onNext(400, 5),
            onCompleted(420)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->distinct();
        });

        $this->assertMessages([
            onNext(280, 4),
            onNext(300, 2),
            onNext(350, 1),
            onNext(380, 3),
            onNext(400, 5),
            onCompleted(420)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 420)
        ], $xs->getSubscriptions());

    }


    /**
     * @test
     */
    public function distinct_default_comparer_some_duplicates()
    {
        $xs = $this->createHotObservable([
            onNext(280, 4),
            onNext(300, 2),
            onNext(350, 2),
            onNext(380, 3),
            onNext(400, 4),
            onCompleted(420)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->distinct();
        });

        $this->assertMessages([
            onNext(280, 4),
            onNext(300, 2),
            onNext(380, 3),
            onCompleted(420)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 420)
        ], $xs->getSubscriptions());

    }


    /**
     * @test
     */
    public function distinct_default_comparer_some_error()
    {
        $error = new \Exception();

        $xs = $this->createHotObservable([
            onNext(280, 4),
            onNext(300, 2),
            onError(350, $error),
            onNext(380, 3),
            onNext(400, 4),
            onCompleted(420)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->distinct();
        });

        $this->assertMessages([
            onNext(280, 4),
            onNext(300, 2),
            onError(350, $error)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 350)
        ], $xs->getSubscriptions());

    }

    /**
     * @test
     */
    public function distinct_default_comparer_dispose()
    {
        $xs = $this->createHotObservable([
            onNext(280, 4),
            onNext(300, 2),
            onNext(350, 2),
            onNext(380, 3),
            onNext(400, 4),
            onCompleted(420)
        ]);

        $results = $this->scheduler->startWithDispose(function () use ($xs) {
            return $xs->distinct();
        }, 360);

        $this->assertMessages([
            onNext(280, 4),
            onNext(300, 2)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 360)
        ], $xs->getSubscriptions());

    }


    /**
     * @test
     */
    public function distinct_CustomComparer_all_distinct()
    {
        $xs = $this->createHotObservable([
            onNext(280, 4),
            onNext(300, 2),
            onNext(350, 1),
            onNext(380, 3),
            onNext(400, 5),
            onCompleted(420)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->distinct(call_user_func([$this, "modComparer"], 10));
        });

        $this->assertMessages([
            onNext(280, 4),
            onNext(300, 2),
            onNext(350, 1),
            onNext(380, 3),
            onNext(400, 5),
            onCompleted(420)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 420)
        ], $xs->getSubscriptions());

    }


    /**
     * @test
     */
    public function distinct_CustomComparer_some_duplicates()
    {
        $xs = $this->createHotObservable([
            onNext(280, 4),
            onNext(300, 2),
            onNext(350, 12),
            onNext(380, 3),
            onNext(400, 24),
            onCompleted(420)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->distinct(call_user_func([$this, "modComparer"], 10));
        });

        $this->assertMessages([
            onNext(280, 4),
            onNext(300, 2),
            onNext(380, 3),
            onCompleted(420)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 420)
        ], $xs->getSubscriptions());

    }

    /**
     *
     * @test
     */
    public function distinct_CustomComparer_some_throw()
    {
        $error = new \Exception();

        $xs = $this->createHotObservable([
            onNext(280, 4),
            onNext(300, 2),
            onNext(350, 12),
            onNext(380, 3),
            onNext(400, 24),
            onCompleted(420)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs, $error) {
            return $xs->distinct(function ($x) use ($error) {
                if ($x === 12) {
                    throw $error;
                }
            });
        });

        $this->assertMessages([
            onNext(280, 4),
            onNext(300, 2),
            onError(350, $error)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 350)
        ], $xs->getSubscriptions());

    }


    /**
     * @test
     */
    public function distinct_CustomKey_all_distinct()
    {
        $xs = $this->createHotObservable([
            onNext(280, ['id' => 4]),
            onNext(300, ['id' => 2]),
            onNext(350, ['id' => 1]),
            onNext(380, ['id' => 3]),
            onNext(400, ['id' => 5]),
            onCompleted(420)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->distinctKey(function ($x) {
                return $x['id'];
            })->map(function ($x) {
                return $x['id'];
            });
        });

        $this->assertMessages([
            onNext(280, 4),
            onNext(300, 2),
            onNext(350, 1),
            onNext(380, 3),
            onNext(400, 5),
            onCompleted(420)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 420)
        ], $xs->getSubscriptions());

    }


    /**
     * @test
     */
    public function distinct_CustomKey_some_duplicates()
    {

        $xs = $this->createHotObservable([
            onNext(280, ['id' => 4]),
            onNext(300, ['id' => 2]),
            onNext(350, ['id' => 4]),
            onNext(380, ['id' => 3]),
            onNext(400, ['id' => 3]),
            onCompleted(420)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->distinctKey(function ($x) {
                return $x['id'];
            })->map(function ($x) {
                return $x['id'];
            });
        });

        $this->assertMessages([
            onNext(280, 4),
            onNext(300, 2),
            onNext(380, 3),
            onCompleted(420)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 420)
        ], $xs->getSubscriptions());

    }

    /**
     *
     * @test
     */
    public function distinct_CustomKey_some_throw()
    {
        $error = new \Exception();

        $xs = $this->createHotObservable([
            onNext(280, ['id' => 4]),
            onNext(300, ['id' => 2]),
            onNext(350, ['id' => 4]),
            onNext(380, ['id' => 3]),
            onNext(400, ['id' => 3]),
            onCompleted(420)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs, $error) {
            return $xs->distinctKey(function ($x) use ($error) {
                if ($x['id'] === 3) {
                    throw $error;
                }
                return $x['id'];
            })->map(function ($x) {
                return $x['id'];
            });
        });

        $this->assertMessages([
            onNext(280, 4),
            onNext(300, 2),
            onError(380, $error)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 380)
        ], $xs->getSubscriptions());

    }

    /**
     * @test
     */
    public function distinct_CustomKey_and_CustomComparer_some_duplicates()
    {

        $xs = $this->createHotObservable([
            onNext(280, ['id' => 4]),
            onNext(300, ['id' => 2]),
            onNext(350, ['id' => 12]),
            onNext(380, ['id' => 3]),
            onNext(400, ['id' => 24]),
            onCompleted(420)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->distinctKey(
                function ($x) {
                    return $x['id'];
                },
                $this->modComparer(10)
            )->map(function ($x) {
                return $x['id'];
            });
        });

        $this->assertMessages([
            onNext(280, 4),
            onNext(300, 2),
            onNext(380, 3),
            onCompleted(420)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 420)
        ], $xs->getSubscriptions());

    }


    public function modComparer($mod)
    {
        return function ($x, $y) use ($mod) {
            $comparer = function ($x, $y) {
                return $x == $y;
            };

            return $comparer($x % $mod, $y % $mod);
        };
    }

}
