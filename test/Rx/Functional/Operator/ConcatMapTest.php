<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable;
use Exception;

class ConcatMapTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function concatMapTo_Then_Complete_Task()
    {
        $xs = Observable::fromArray([4, 3, 2, 1]);
        $ys = Observable::of(42);

        $results   = [];
        $completed = false;

        $xs->concatMapTo($ys)
            ->subscribe(
                function ($x) use (&$results) {
                    $results[] = $x;
                },
                function ($e) {
                    $this->fail();
                },
                function () use (&$completed) {
                    $completed = true;
                }
            );

        $this->assertTrue($completed);
        $this->assertEquals([42, 42, 42, 42], $results);
    }

    /**
     * @test
     */
    public function concatMapTo_Then_Error_Task()
    {
        $xs = Observable::fromArray([4, 3, 2, 1]);
        $ys = Observable::error(new \Exception('test'));

        $results   = [];
        $completed = false;
        $error     = false;

        $xs->concatMapTo($ys)
            ->subscribe(
                function ($x) use (&$results) {
                    $results[] = $x;
                },
                function (\Exception $e) use (&$results, &$error) {
                    $error = true;
                    $this->assertSame('test', $e->getMessage());
                },
                function () use (&$completed) {
                    $completed = true;
                }
            );

        $this->assertFalse($completed);
        $this->assertTrue($error);
    }

    /**
     * @test
     */
    public function concatMap_result_Complete_Task()
    {
        $xs = Observable::fromArray([4, 3, 2, 1]);

        $results   = [];
        $completed = false;

        $xs->concatMap(
            function ($x, $i) {
                return Observable::of($x + $i);
            },
            function ($x, $y, $oi, $ii) {
                $this->assertEquals(0, $ii);
                return $x + $y + $oi;
            })
            ->subscribe(
                function ($x) use (&$results) {
                    $results[] = $x;
                },
                function ($e) {
                    $this->fail();
                },
                function () use (&$completed) {
                    $completed = true;
                }
            );

        $this->assertTrue($completed);
        $this->assertEquals([8, 8, 8, 8], $results);
    }

    /**
     * @test
     */
    public function concatMap_result_Error_Task()
    {
        $xs = Observable::fromArray([4, 3, 2, 1]);

        $results     = [];
        $completed   = false;
        $error       = new \Exception();
        $returnError = null;

        $xs->concatMap(
            function ($x, $i) {
                return Observable::of($x + $i);
            },
            function ($x, $y, $i) use ($error) {
                throw $error;
            })
            ->subscribe(
                function ($x) use (&$results) {
                    $results[] = $x;
                },
                function ($e) use (&$returnError) {
                    $returnError = $e;
                },
                function () use (&$completed) {
                    $completed = true;
                }
            );

        $this->assertFalse($completed);
        $this->assertSame($error, $returnError);
        $this->assertEquals([], $results);
    }

    /**
     * @test
     */
    public function concatMap_Then_Complete_Task()
    {
        $xs = Observable::fromArray([4, 3, 2, 1]);

        $results   = [];
        $completed = false;

        $xs->concatMap(function ($x, $i) {
            return Observable::of($x + $i);
        })
            ->subscribe(
                function ($x) use (&$results) {
                    $results[] = $x;
                },
                function ($e) {
                    $this->fail('Should not get an error');
                },
                function () use (&$completed) {
                    $completed = true;
                }
            );

        $this->assertTrue($completed);
        $this->assertEquals([4, 4, 4, 4], $results);
    }

    /**
     * @test
     */
    public function concatMap_Then_Error_Task()
    {
        $xs = Observable::fromArray([4, 3, 2, 1]);

        $results   = [];
        $completed = false;
        $error     = false;

        $xs->concatMap(function ($x, $i) {
            return Observable::error(new Exception((string)($x + $i)));
        })
            ->subscribe(
                function ($x) use (&$results) {
                    $results[] = $x;
                },
                function (\Exception $e) use (&$results, &$error) {
                    $error = true;
                    $this->assertEquals(4, $e->getMessage());
                },
                function () use (&$completed) {
                    $completed = true;
                }
            );

        $this->assertFalse($completed);
        $this->assertTrue($error);
    }

    /**
     * @test
     */
    public function concatMapTo_Then_Complete_Complete()
    {

        $xs = $this->createColdObservable(
            [
                onNext(100, 4),
                onNext(200, 2),
                onNext(300, 3),
                onNext(400, 1),
                onCompleted(500)
            ]
        );

        $ys = $this->createColdObservable(
            [
                onNext(50, 'foo'),
                onNext(100, 'bar'),
                onNext(150, 'baz'),
                onNext(200, 'qux'),
                onCompleted(250)
            ]
        );

        $results = $this->scheduler->startWithDispose(function () use ($xs, $ys) {
            return $xs->concatMapTo($ys);
        }, 2000);

        $this->assertMessages(
            [
                onNext(350, 'foo'),
                onNext(400, 'bar'),
                onNext(450, 'baz'),
                onNext(500, 'qux'),
                onNext(600, 'foo'),
                onNext(650, 'bar'),
                onNext(700, 'baz'),
                onNext(750, 'qux'),
                onNext(850, 'foo'),
                onNext(900, 'bar'),
                onNext(950, 'baz'),
                onNext(1000, 'qux'),
                onNext(1100, 'foo'),
                onNext(1150, 'bar'),
                onNext(1200, 'baz'),
                onNext(1250, 'qux'),
                onCompleted(1300)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions([
            subscribe(200, 700)
        ], $xs->getSubscriptions());

        $this->assertSubscriptions([
            subscribe(300, 550),
            subscribe(550, 800),
            subscribe(800, 1050),
            subscribe(1050, 1300)
        ], $ys->getSubscriptions());
    }

    /**
     * @test
     */
    public function concatMapTo_Then_Complete_Complete_2()
    {

        $xs = $this->createColdObservable(
            [
                onNext(100, 4),
                onNext(200, 2),
                onNext(300, 3),
                onNext(400, 1),
                onCompleted(700)
            ]
        );

        $ys = $this->createColdObservable(
            [
                onNext(50, 'foo'),
                onNext(100, 'bar'),
                onNext(150, 'baz'),
                onNext(200, 'qux'),
                onCompleted(250)
            ]
        );

        $results = $this->scheduler->startWithDispose(function () use ($xs, $ys) {
            return $xs->concatMapTo($ys);
        }, 2000);

        $this->assertMessages(
            [
                onNext(350, 'foo'),
                onNext(400, 'bar'),
                onNext(450, 'baz'),
                onNext(500, 'qux'),
                onNext(600, 'foo'),
                onNext(650, 'bar'),
                onNext(700, 'baz'),
                onNext(750, 'qux'),
                onNext(850, 'foo'),
                onNext(900, 'bar'),
                onNext(950, 'baz'),
                onNext(1000, 'qux'),
                onNext(1100, 'foo'),
                onNext(1150, 'bar'),
                onNext(1200, 'baz'),
                onNext(1250, 'qux'),
                onCompleted(1300)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions([
            subscribe(200, 900)
        ], $xs->getSubscriptions());

        $this->assertSubscriptions([
            subscribe(300, 550),
            subscribe(550, 800),
            subscribe(800, 1050),
            subscribe(1050, 1300)
        ], $ys->getSubscriptions());
    }

    /**
     * @test
     */
    public function concatMapTo_Then_Never_Complete()
    {

        $xs = $this->createColdObservable(
            [
                onNext(100, 4),
                onNext(200, 2),
                onNext(300, 3),
                onNext(400, 1),
                onNext(500, 5),
                onNext(700, 0)
            ]
        );

        $ys = $this->createColdObservable(
            [
                onNext(50, 'foo'),
                onNext(100, 'bar'),
                onNext(150, 'baz'),
                onNext(200, 'qux'),
                onCompleted(250)
            ]
        );

        $results = $this->scheduler->startWithDispose(function () use ($xs, $ys) {
            return $xs->concatMapTo($ys);
        }, 2000);

        $this->assertMessages(
            [
                onNext(350, 'foo'),
                onNext(400, 'bar'),
                onNext(450, 'baz'),
                onNext(500, 'qux'),
                onNext(600, 'foo'),
                onNext(650, 'bar'),
                onNext(700, 'baz'),
                onNext(750, 'qux'),
                onNext(850, 'foo'),
                onNext(900, 'bar'),
                onNext(950, 'baz'),
                onNext(1000, 'qux'),
                onNext(1100, 'foo'),
                onNext(1150, 'bar'),
                onNext(1200, 'baz'),
                onNext(1250, 'qux'),
                onNext(1350, 'foo'),
                onNext(1400, 'bar'),
                onNext(1450, 'baz'),
                onNext(1500, 'qux'),
                onNext(1600, 'foo'),
                onNext(1650, 'bar'),
                onNext(1700, 'baz'),
                onNext(1750, 'qux')
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions([
            subscribe(200, 2000)
        ], $xs->getSubscriptions());

        $this->assertSubscriptions([
            subscribe(300, 550),
            subscribe(550, 800),
            subscribe(800, 1050),
            subscribe(1050, 1300),
            subscribe(1300, 1550),
            subscribe(1550, 1800)
        ], $ys->getSubscriptions());
    }

    /**
     * @test
     */
    public function concatMapTo_Then_Complete_Never()
    {

        $xs = $this->createColdObservable(
            [
                onNext(100, 4),
                onNext(200, 2),
                onNext(300, 3),
                onNext(400, 1),
                onCompleted(500)
            ]
        );

        $ys = $this->createColdObservable(
            [
                onNext(50, 'foo'),
                onNext(100, 'bar'),
                onNext(150, 'baz'),
                onNext(200, 'qux')
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($xs, $ys) {
            return $xs->concatMapTo($ys);
        });

        $this->assertMessages(
            [
                onNext(350, 'foo'),
                onNext(400, 'bar'),
                onNext(450, 'baz'),
                onNext(500, 'qux')
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions([
            subscribe(200, 700)
        ], $xs->getSubscriptions());

        $this->assertSubscriptions([
            subscribe(300, 1000)
        ], $ys->getSubscriptions());
    }

    /**
     * @test
     */
    public function concatMapTo_Then_Complete_Error()
    {

        $ex = new Exception();

        $xs = $this->createColdObservable(
            [
                onNext(100, 4),
                onNext(200, 2),
                onNext(300, 3),
                onNext(400, 1),
                onCompleted(500)
            ]
        );

        $ys = $this->createColdObservable(
            [
                onNext(50, 'foo'),
                onNext(100, 'bar'),
                onNext(150, 'baz'),
                onNext(200, 'qux'),
                onError(300, $ex)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($xs, $ys) {
            return $xs->concatMapTo($ys);
        });

        $this->assertMessages(
            [
                onNext(350, 'foo'),
                onNext(400, 'bar'),
                onNext(450, 'baz'),
                onNext(500, 'qux'),
                onError(600, $ex)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions([
            subscribe(200, 600)
        ], $xs->getSubscriptions());

        $this->assertSubscriptions([
            subscribe(300, 600)
        ], $ys->getSubscriptions());
    }

    /**
     * @test
     */
    public function concatMapTo_Then_Error_Complete()
    {

        $ex = new Exception();

        $xs = $this->createColdObservable(
            [
                onNext(100, 4),
                onNext(200, 2),
                onNext(300, 3),
                onNext(400, 1),
                onError(500, $ex)
            ]
        );

        $ys = $this->createColdObservable(
            [
                onNext(50, 'foo'),
                onNext(100, 'bar'),
                onNext(150, 'baz'),
                onNext(200, 'qux'),
                onCompleted(250)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($xs, $ys) {
            return $xs->concatMapTo($ys);
        });

        $this->assertMessages(
            [
                onNext(350, 'foo'),
                onNext(400, 'bar'),
                onNext(450, 'baz'),
                onNext(500, 'qux'),
                onNext(600, 'foo'),
                onNext(650, 'bar'),
                onError(700, $ex)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions([
            subscribe(200, 700)
        ], $xs->getSubscriptions());

        $this->assertSubscriptions([
            subscribe(300, 550),
            subscribe(550, 700)
        ], $ys->getSubscriptions());
    }

    /**
     * @test
     */
    public function concatMapTo_Then_Error_Error()
    {

        $ex = new Exception();

        $xs = $this->createColdObservable(
            [
                onNext(100, 4),
                onNext(200, 2),
                onNext(300, 3),
                onNext(400, 1),
                onError(500, $ex)
            ]
        );

        $ys = $this->createColdObservable(
            [
                onNext(50, 'foo'),
                onNext(100, 'bar'),
                onNext(150, 'baz'),
                onNext(200, 'qux'),
                onError(250, $ex)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($xs, $ys) {
            return $xs->concatMapTo($ys);
        });

        $this->assertMessages(
            [
                onNext(350, 'foo'),
                onNext(400, 'bar'),
                onNext(450, 'baz'),
                onNext(500, 'qux'),
                onError(550, $ex)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions([
            subscribe(200, 550)
        ], $xs->getSubscriptions());

        $this->assertSubscriptions([
            subscribe(300, 550)
        ], $ys->getSubscriptions());
    }

    /**
     * @test
     */
    public function concatMap_Complete()
    {

        $xs = $this->createHotObservable([
            onNext(5, $this->createColdObservable([
                onError(1, new Exception('ex1'))
            ])),
            onNext(105, $this->createColdObservable([
                onError(1, new Exception('ex2'))
            ])),
            onNext(300, $this->createColdObservable([
                onNext(10, 102),
                onNext(90, 103),
                onNext(110, 104),
                onNext(190, 105),
                onNext(440, 106),
                onCompleted(460)
            ])),
            onNext(400, $this->createColdObservable([
                onNext(180, 202),
                onNext(190, 203),
                onCompleted(205)
            ])),
            onNext(550, $this->createColdObservable([
                onNext(10, 301),
                onNext(50, 302),
                onNext(70, 303),
                onNext(260, 304),
                onNext(310, 305),
                onCompleted(410)
            ])),
            onNext(750, $this->createColdObservable([
                onCompleted(40)
            ])),
            onNext(850, $this->createColdObservable([
                onNext(80, 401),
                onNext(90, 402),
                onCompleted(100)
            ])),
            onCompleted(900)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->concatMap(function (Observable $obs) {
                return $obs;
            });
        });

        $this->assertMessages(
            [
                onNext(310, 102),
                onNext(390, 103),
                onNext(410, 104),
                onNext(490, 105),
                onNext(740, 106),
                onNext(940, 202),
                onNext(950, 203),
                onNext(975, 301)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions([
            subscribe(200, 900)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function concatMap_Complete_OuterNotComplete()
    {


        $xs = $this->createHotObservable([
            onNext(5, $this->createColdObservable([
                onError(1, new Exception('ex1'))
            ])),
            onNext(105, $this->createColdObservable([
                onError(1, new Exception('ex2'))
            ])),
            onNext(300, $this->createColdObservable([
                onNext(10, 102),
                onNext(90, 103),
                onNext(110, 104),
                onNext(190, 105),
                onNext(440, 106),
                onCompleted(460)
            ])),
            onNext(400, $this->createColdObservable([
                onNext(180, 202),
                onNext(190, 203),
                onCompleted(205)
            ])),
            onNext(550, $this->createColdObservable([
                onNext(10, 301),
                onNext(50, 302),
                onNext(70, 303),
                onNext(260, 304),
                onNext(310, 305),
                onCompleted(410)
            ])),
            onNext(750, $this->createColdObservable([
                onCompleted(40)
            ])),
            onNext(850, $this->createColdObservable([
                onNext(80, 401),
                onNext(90, 402),
                onCompleted(100)
            ]))
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->concatMap(function (Observable $obs) {
                return $obs;
            });
        });

        $this->assertMessages(
            [
                onNext(310, 102),
                onNext(390, 103),
                onNext(410, 104),
                onNext(490, 105),
                onNext(740, 106),
                onNext(940, 202),
                onNext(950, 203),
                onNext(975, 301)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions([
            subscribe(200, 1000)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function concatMap_Error_Outer()
    {
        $ex = new Exception();

        $xs = $this->createHotObservable([
            onNext(5, $this->createColdObservable([
                onError(1, new Exception('ex1'))
            ])),
            onNext(105, $this->createColdObservable([
                onError(1, new Exception('ex2'))
            ])),
            onNext(300, $this->createColdObservable([
                onNext(10, 102),
                onNext(90, 103),
                onNext(110, 104),
                onNext(190, 105),
                onNext(440, 106),
                onCompleted(460)
            ])),
            onNext(400, $this->createColdObservable([
                onNext(180, 202),
                onNext(190, 203),
                onCompleted(205)
            ])),
            onNext(550, $this->createColdObservable([
                onNext(10, 301),
                onNext(50, 302),
                onNext(70, 303),
                onNext(260, 304),
                onNext(310, 305),
                onCompleted(410)
            ])),
            onNext(750, $this->createColdObservable([
                onCompleted(40)
            ])),
            onNext(850, $this->createColdObservable([
                onNext(80, 401),
                onNext(90, 402),
                onCompleted(100)
            ])),
            onError(900, $ex)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->concatMap(function (Observable $obs) {
                return $obs;
            });
        });

        $this->assertMessages(
            [
                onNext(310, 102),
                onNext(390, 103),
                onNext(410, 104),
                onNext(490, 105),
                onNext(740, 106),
                onError(900, $ex)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions([
            subscribe(200, 900)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function concatMap_Error_Inner()
    {
        $ex = new Exception();

        $xs = $this->createHotObservable([
            onNext(5, $this->createColdObservable([
                onError(1, new Exception('ex1'))
            ])),
            onNext(105, $this->createColdObservable([
                onError(1, new Exception('ex2'))
            ])),
            onNext(300, $this->createColdObservable([
                onNext(10, 102),
                onNext(90, 103),
                onNext(110, 104),
                onNext(190, 105),
                onNext(440, 106),
                onError(460, $ex)
            ])),
            onNext(400, $this->createColdObservable([
                onNext(180, 202),
                onNext(190, 203),
                onCompleted(205)
            ])),
            onNext(550, $this->createColdObservable([
                onNext(10, 301),
                onNext(50, 302),
                onNext(70, 303),
                onNext(260, 304),
                onNext(310, 305),
                onCompleted(410)
            ])),
            onNext(750, $this->createColdObservable([
                onCompleted(40)
            ])),
            onNext(850, $this->createColdObservable([
                onNext(80, 401),
                onNext(90, 402),
                onCompleted(100)
            ])),
            onCompleted(900)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->concatMap(function (Observable $obs) {
                return $obs;
            });
        });

        $this->assertMessages(
            [
                onNext(310, 102),
                onNext(390, 103),
                onNext(410, 104),
                onNext(490, 105),
                onNext(740, 106),
                onError(760, $ex)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions([
            subscribe(200, 760)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function concatMap_Dispose()
    {

        $xs = $this->createHotObservable([
            onNext(5, $this->createColdObservable([
                onError(1, new Exception('ex1'))
            ])),
            onNext(105, $this->createColdObservable([
                onError(1, new Exception('ex2'))
            ])),
            onNext(300, $this->createColdObservable([
                onNext(10, 102),
                onNext(90, 103),
                onNext(110, 104),
                onNext(190, 105),
                onNext(440, 106),
                onCompleted(460)
            ])),
            onNext(400, $this->createColdObservable([
                onNext(180, 202),
                onNext(190, 203),
                onCompleted(205)
            ])),
            onNext(550, $this->createColdObservable([
                onNext(10, 301),
                onNext(50, 302),
                onNext(70, 303),
                onNext(260, 304),
                onNext(310, 305),
                onCompleted(410)
            ])),
            onNext(750, $this->createColdObservable([
                onCompleted(40)
            ])),
            onNext(850, $this->createColdObservable([
                onNext(80, 401),
                onNext(90, 402),
                onCompleted(100)
            ])),
            onCompleted(900)
        ]);

        $results = $this->scheduler->startWithDispose(function () use ($xs) {
            return $xs->concatMap(function (Observable $obs) {
                return $obs;
            });
        }, 700);

        $this->assertMessages(
            [
                onNext(310, 102),
                onNext(390, 103),
                onNext(410, 104),
                onNext(490, 105)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions([
            subscribe(200, 700)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function concatMap_Throw()
    {
        $invoked = 0;
        $ex      = new Exception('ex');

        $xs = $this->createHotObservable([
            onNext(5, $this->createColdObservable([
                onError(1, new Exception('ex1'))
            ])),
            onNext(105, $this->createColdObservable([
                onError(1, new Exception('ex2'))
            ])),
            onNext(300, $this->createColdObservable([
                onNext(10, 102),
                onNext(90, 103),
                onNext(110, 104),
                onNext(190, 105),
                onNext(440, 106),
                onCompleted(460)
            ])),
            onNext(400, $this->createColdObservable([
                onNext(180, 202),
                onNext(190, 203),
                onCompleted(205)
            ])),
            onNext(550, $this->createColdObservable([
                onNext(10, 301),
                onNext(50, 302),
                onNext(70, 303),
                onNext(260, 304),
                onNext(310, 305),
                onCompleted(410)
            ])),
            onNext(750, $this->createColdObservable([
                onCompleted(40)
            ])),
            onNext(850, $this->createColdObservable([
                onNext(80, 401),
                onNext(90, 402),
                onCompleted(100)
            ])),
            onCompleted(900)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs, &$invoked, $ex) {
            return $xs->concatMap(function (Observable $obs) use (&$invoked, $ex) {
                $invoked++;
                if ($invoked === 3) {
                    throw $ex;
                }
                return $obs;
            });
        });

        $this->assertMessages(
            [
                onNext(310, 102),
                onNext(390, 103),
                onNext(410, 104),
                onNext(490, 105),
                onError(550, $ex)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions([
            subscribe(200, 550)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function concatMap_UseFunction()
    {
        $xs = $this->createHotObservable([
            onNext(210, 4),
            onNext(220, 3),
            onNext(250, 5),
            onNext(270, 1),
            onCompleted(290)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->concatMap(function ($x) {
                return Observable::interval(10, $this->scheduler)
                    ->map(function () use ($x) {
                        return $x;
                    })->take($x);
            });
        });

        $this->assertMessages(
            [
                onNext(220, 4),
                onNext(230, 4),
                onNext(240, 4),
                onNext(250, 4),
                onNext(260, 3),
                onNext(270, 3),
                onNext(280, 3),
                onNext(290, 5),
                onNext(300, 5),
                onNext(310, 5),
                onNext(320, 5),
                onNext(330, 5),
                onNext(340, 1),
                onCompleted(340)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions([
            subscribe(200, 290)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function concatMap_return_invalid_string()
    {
        $xs = $this->createHotObservable([
            onNext(5, $this->createColdObservable([
                onError(1, new Exception('ex1'))
            ])),
            onNext(105, $this->createColdObservable([
                onError(1, new Exception('ex2'))
            ])),
            onNext(300, $this->createColdObservable([
                onNext(10, 102),
                onNext(90, 103),
                onNext(110, 104),
                onNext(190, 105),
                onNext(440, 106),
                onCompleted(460)
            ])),
            onNext(400, $this->createColdObservable([
                onNext(180, 202),
                onNext(190, 203),
                onCompleted(205)
            ])),
            onNext(550, $this->createColdObservable([
                onNext(10, 301),
                onNext(50, 302),
                onNext(70, 303),
                onNext(260, 304),
                onNext(310, 305),
                onCompleted(410)
            ])),
            onNext(750, $this->createColdObservable([
                onCompleted(40)
            ])),
            onNext(850, $this->createColdObservable([
                onNext(80, 401),
                onNext(90, 402),
                onCompleted(100)
            ])),
            onCompleted(900)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->concatMap(function (Observable $obs) {
                return 'unexpected string';
            });
        });

        $this->assertMessages(
            [
                onError(300, new Exception())
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions([subscribe(200, 300)], $xs->getSubscriptions());
    }
}
