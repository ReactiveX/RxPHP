<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Exception;
use Rx\Disposable\SerialDisposable;
use Rx\Functional\FunctionalTestCase;
use Rx\Testing\TestScheduler;

class SelectTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function calls_on_error_if_selector_throws_an_exception()
    {
        $xs = $this->createHotObservable([
            onNext(500, 42),
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->select(function () {
                throw new Exception();
            });
        });

        $this->assertMessages([onError(500, new Exception())], $results->getMessages());
    }

    /**
     * @test
     */
    public function select_calls_on_completed()
    {
        $xs = $this->createHotObservable([
            onCompleted(500),
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->select('RxIdentity');
        });

        $this->assertMessages([onCompleted(500)], $results->getMessages());
    }

    /**
     * @test
     */
    public function select_calls_on_error()
    {
        $xs = $this->createHotObservable([
            onError(500, new Exception()),
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->select('RxIdentity');
        });

        $this->assertMessages([onError(500, new Exception())], $results->getMessages());
    }

    /**
     * @test
     */
    public function select_calls_selector()
    {
        $xs = $this->createHotObservable([
            onNext(100, 2),
            onNext(300, 21),
            onNext(500, 42),
            onNext(800, 84),
            onCompleted(820),
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->select(function ($elem) {
                return $elem * 2;
            });
        });

        $this->assertMessages([
            onNext(300, 42),
            onNext(500, 84),
            onNext(800, 168),
            onCompleted(820),
        ], $results->getMessages());

    }

    /**
     * @test
     */
    public function map_with_index_dispose_inside_selector()
    {
        $xs = $this->createHotObservable([
            onNext(100, 4),
            onNext(200, 3),
            onNext(500, 2),
            onNext(600, 1)
        ]);

        $invoked = 0;

        $results = $this->scheduler->createObserver();

        $d = new SerialDisposable();

        $d->setDisposable(
            $xs->mapWithIndex(function ($index, $x) use (&$invoked, $d) {
                $invoked++;

                if ($this->scheduler->getClock() > 400) {
                    $d->dispose();
                }
                return $x + $index * 10;
            })->subscribe($results)
        );

        $this->scheduler->scheduleAbsolute(TestScheduler::DISPOSED, function () use ($d) {
            $d->dispose();
        });

        $this->scheduler->start();

        $this->assertMessages([
            onNext(100, 4),
            onNext(200, 13)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(0, 500)
        ], $xs->getSubscriptions());

        $this->assertEquals(3, $invoked);
    }


    /**
     * @test
     */
    public function map_with_index_completed()
    {
        $xs = $this->createHotObservable([
            onNext(180, 5),
            onNext(210, 4),
            onNext(240, 3),
            onNext(290, 2),
            onNext(350, 1),
            onCompleted(400),
            onNext(410, -1),
            onCompleted(420),
            onError(430, new Exception())
        ]);

        $invoked = 0;

        $results = $this->scheduler->startWithCreate(function () use ($xs, &$invoked) {
            return $xs->mapWithIndex(function ($index, $x) use (&$invoked) {
                $invoked++;

                return ($x + 1) + ($index * 10);
            });
        });

        $this->assertMessages([
            onNext(210, 5),
            onNext(240, 14),
            onNext(290, 23),
            onNext(350, 32),
            onCompleted(400)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 400)
        ], $xs->getSubscriptions());

        $this->assertEquals(4, $invoked);
    }

    /**
     * @test
     */
    public function map_with_index_not_completed()
    {
        $xs = $this->createHotObservable([
            onNext(180, 5),
            onNext(210, 4),
            onNext(240, 3),
            onNext(290, 2),
            onNext(350, 1)
        ]);

        $invoked = 0;

        $results = $this->scheduler->startWithCreate(function () use ($xs, &$invoked) {
            return $xs->mapWithIndex(function ($index, $x) use (&$invoked) {
                $invoked++;

                return ($x + 1) + ($index * 10);
            });
        });

        $this->assertMessages([
            onNext(210, 5),
            onNext(240, 14),
            onNext(290, 23),
            onNext(350, 32)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 1000)
        ], $xs->getSubscriptions());

        $this->assertEquals(4, $invoked);
    }

    /**
     * @test
     */
    public function map_with_index_error()
    {
        $error = new Exception();

        $xs = $this->createHotObservable([
            onNext(180, 5),
            onNext(210, 4),
            onNext(240, 3),
            onNext(290, 2),
            onNext(350, 1),
            onError(400, $error),
            onNext(410, -1),
            onCompleted(420),
            onError(430, new Exception())
        ]);

        $invoked = 0;

        $results = $this->scheduler->startWithCreate(function () use ($xs, &$invoked) {
            return $xs->mapWithIndex(function ($index, $x) use (&$invoked) {
                $invoked++;

                return ($x + 1) + ($index * 10);
            });
        });

        $this->assertMessages([
            onNext(210, 5),
            onNext(240, 14),
            onNext(290, 23),
            onNext(350, 32),
            onError(400, $error)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 400)
        ], $xs->getSubscriptions());

        $this->assertEquals(4, $invoked);
    }

    /**
     * @test
     */
    public function map_with_index_throws()
    {
        $error = new Exception();

        $xs = $this->createHotObservable([
            onNext(180, 5),
            onNext(210, 4),
            onNext(240, 3),
            onNext(290, 2),
            onNext(350, 1),
            onError(400, $error),
            onNext(410, -1),
            onCompleted(420),
            onError(430, new Exception())
        ]);

        $invoked = 0;

        $results = $this->scheduler->startWithCreate(function () use ($xs, &$invoked) {
            return $xs->mapWithIndex(function ($index, $x) use (&$invoked) {
                $invoked++;
                if ($invoked === 3) {
                    throw new Exception;
                }
                return ($x + 1) + ($index * 10);
            });
        });

        $this->assertMessages([
            onNext(210, 5),
            onNext(240, 14),
            onError(290, $error)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 290)
        ], $xs->getSubscriptions());

        $this->assertEquals(3, $invoked);
    }

    /**
     * @test
     */
    public function mapTo_value()
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
            return $xs->mapTo(-1);
        });

        $this->assertMessages([
            onNext(210, -1),
            onNext(220, -1),
            onNext(230, -1),
            onNext(240, -1),
            onCompleted(250)
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function map_and_map_Optimization()
    {

        $invoked1 = 0;
        $invoked2 = 0;

        $xs = $this->createHotObservable([
            onNext(110, 1),
            onNext(180, 2),
            onNext(230, 3),
            onNext(270, 4),
            onNext(340, 5),
            onNext(380, 6),
            onNext(390, 7),
            onNext(450, 8),
            onNext(470, 9),
            onNext(560, 10),
            onNext(580, 11),
            onCompleted(600),
            onNext(610, 12),
            onError(620, new Exception()),
            onCompleted(630)
        ]);


        $results = $this->scheduler->startWithCreate(function () use ($xs, &$invoked1, &$invoked2) {
            return $xs
                ->map(function ($x) use (&$invoked1) {
                    $invoked1++;

                    return $x * 2;
                })
                ->map(function ($x) use (&$invoked2) {
                    $invoked2++;

                    return $x / 2;
                });
        });

        $this->assertMessages([
            onNext(230, 3),
            onNext(270, 4),
            onNext(340, 5),
            onNext(380, 6),
            onNext(390, 7),
            onNext(450, 8),
            onNext(470, 9),
            onNext(560, 10),
            onNext(580, 11),
            onCompleted(600)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 600)
        ], $xs->getSubscriptions());

        $this->assertEquals(9, $invoked1);
        $this->assertEquals(9, $invoked2);
    }
}
