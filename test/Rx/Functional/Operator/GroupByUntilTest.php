<?php

declare(strict_types=1);

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Notification;
use Rx\Observable;
use Rx\Observable\GroupedObservable;
use Rx\Testing\TestScheduler;

class GroupByUntilTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function groupByUntilWithKeyComparer()
    {
        $keyInvoked = 0;

        $xs = $this->createHotObservable(
            [
                onNext(90, new \Exception()),
                onNext(110, new \Exception()),
                onNext(130, new \Exception()),
                onNext(220, '  foo'),
                onNext(240, ' FoO '),
                onNext(270, 'baR  '),
                onNext(310, 'foO '),
                onNext(350, ' Baz   '),
                onNext(360, '  qux '),
                onNext(390, '   bar'),
                onNext(420, ' BAR  '),
                onNext(470, 'FOO '),
                onNext(480, 'baz  '),
                onNext(510, ' bAZ '),
                onNext(530, '    fOo    '),
                onCompleted(570),
                onNext(580, new \Exception()),
                onCompleted(600),
                onError(650, new \Exception())
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($xs, &$keyInvoked) {
            return $xs->groupByUntil(
                function ($x) use (&$keyInvoked) {
                    $keyInvoked++;
                    return trim(strtolower($x));
                },
                function ($x) {
                    return $x;
                },
                function (Observable $g) {
                    return $g->skip(2);
                }
            )->map(function (GroupedObservable $x) {
                return $x->getKey();
            });
        });

        $this->assertMessages(
            [
                onNext(220, 'foo'),
                onNext(270, 'bar'),
                onNext(350, 'baz'),
                onNext(360, 'qux'),
                onNext(470, 'foo'),
                onCompleted(570)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 570)
            ],
            $xs->getSubscriptions()
        );

        $this->assertEquals(12, $keyInvoked);
    }

    /**
     * @test
     */
    public function groupByUntilWithKeyComparerDefaultDurationSelector()
    {
        $keyInvoked = 0;

        $xs = $this->createHotObservable(
            [
                onNext(90, new \Exception()),
                onNext(110, new \Exception()),
                onNext(130, new \Exception()),
                onNext(220, '  foo'),
                onNext(240, ' FoO '),
                onNext(270, 'baR  '),
                onNext(310, 'foO '),
                onNext(350, ' Baz   '),
                onNext(360, '  qux '),
                onNext(390, '   bar'),
                onNext(420, ' BAR  '),
                onNext(470, 'FOO '),
                onNext(480, 'baz  '),
                onNext(510, ' bAZ '),
                onNext(530, '    fOo    '),
                onCompleted(570),
                onNext(580, new \Exception()),
                onCompleted(600),
                onError(650, new \Exception())
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($xs, &$keyInvoked) {
            return $xs->groupByUntil(
                function ($x) use (&$keyInvoked) {
                    $keyInvoked++;
                    return trim(strtolower($x));
                },
                function ($x) {
                    return $x;
                }
            )->map(function (GroupedObservable $x) {
                return $x->getKey();
            });
        });

        $this->assertMessages(
            [
                onNext(220, 'foo'),
                onNext(240, 'foo'),
                onNext(270, 'bar'),
                onNext(310, 'foo'),
                onNext(350, 'baz'),
                onNext(360, 'qux'),
                onNext(390, 'bar'),
                onNext(420, 'bar'),
                onNext(470, 'foo'),
                onNext(480, 'baz'),
                onNext(510, 'baz'),
                onNext(530, 'foo'),
                onCompleted(570)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 570)
            ],
            $xs->getSubscriptions()
        );

        $this->assertEquals(12, $keyInvoked);
    }

    /**
     * @test
     */
    public function groupByUntilOuterComplete()
    {
        $keyInvoked = 0;
        $eleInvoked = 0;

        $xs = $this->createHotObservable(
            [
                onNext(90, new \Exception()),
                onNext(110, new \Exception()),
                onNext(130, new \Exception()),
                onNext(220, '  foo'),
                onNext(240, ' FoO '),
                onNext(270, 'baR  '),
                onNext(310, 'foO '),
                onNext(350, ' Baz   '),
                onNext(360, '  qux '),
                onNext(390, '   bar'),
                onNext(420, ' BAR  '),
                onNext(470, 'FOO '),
                onNext(480, 'baz  '),
                onNext(510, ' bAZ '),
                onNext(530, '    fOo    '),
                onCompleted(570),
                onNext(580, new \Exception()),
                onCompleted(600),
                onError(650, new \Exception())
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($xs, &$keyInvoked, &$eleInvoked) {
            return $xs->groupByUntil(
                function ($x) use (&$keyInvoked) {
                    $keyInvoked++;
                    return trim(strtolower($x));
                },
                function ($x) use (&$eleInvoked) {
                    $eleInvoked++;
                    return strrev($x);
                },
                function (Observable $g) {
                    return $g->skip(2);
                }
            )->map(function (GroupedObservable $x) {
                return $x->getKey();
            });
        });

        $this->assertMessages(
            [
                onNext(220, 'foo'),
                onNext(270, 'bar'),
                onNext(350, 'baz'),
                onNext(360, 'qux'),
                onNext(470, 'foo'),
                onCompleted(570)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 570)
            ],
            $xs->getSubscriptions()
        );

        $this->assertEquals(12, $keyInvoked);
        $this->assertEquals(12, $eleInvoked);
    }

    /**
     * @test
     */
    public function groupByUntilOuterError()
    {
        $error = new \Exception();

        $keyInvoked = 0;
        $eleInvoked = 0;

        $xs = $this->createHotObservable(
            [
                onNext(90, new \Exception()),
                onNext(110, new \Exception()),
                onNext(130, new \Exception()),
                onNext(220, '  foo'),
                onNext(240, ' FoO '),
                onNext(270, 'baR  '),
                onNext(310, 'foO '),
                onNext(350, ' Baz   '),
                onNext(360, '  qux '),
                onNext(390, '   bar'),
                onNext(420, ' BAR  '),
                onNext(470, 'FOO '),
                onNext(480, 'baz  '),
                onNext(510, ' bAZ '),
                onNext(530, '    fOo    '),
                onError(570, $error),
                onNext(580, new \Exception()),
                onCompleted(600),
                onError(650, new \Exception())
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($xs, &$keyInvoked, &$eleInvoked) {
            return $xs->groupByUntil(
                function ($x) use (&$keyInvoked) {
                    $keyInvoked++;
                    return trim(strtolower($x));
                },
                function ($x) use (&$eleInvoked) {
                    $eleInvoked++;
                    return strrev($x);
                },
                function (Observable $g) {
                    return $g->skip(2);
                }
            )->map(function (GroupedObservable $x) {
                return $x->getKey();
            });
        });

        $this->assertMessages(
            [
                onNext(220, 'foo'),
                onNext(270, 'bar'),
                onNext(350, 'baz'),
                onNext(360, 'qux'),
                onNext(470, 'foo'),
                onError(570, $error)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 570)
            ],
            $xs->getSubscriptions()
        );

        $this->assertEquals(12, $keyInvoked);
        $this->assertEquals(12, $eleInvoked);
    }

    /**
     * @test
     */
    public function groupByUntilOuterDispose()
    {
        $keyInvoked = 0;
        $eleInvoked = 0;

        $xs = $this->createHotObservable(
            [
                onNext(90, new \Exception()),
                onNext(110, new \Exception()),
                onNext(130, new \Exception()),
                onNext(220, '  foo'),
                onNext(240, ' FoO '),
                onNext(270, 'baR  '),
                onNext(310, 'foO '),
                onNext(350, ' Baz   '),
                onNext(360, '  qux '),
                onNext(390, '   bar'),
                onNext(420, ' BAR  '),
                onNext(470, 'FOO '),
                onNext(480, 'baz  '),
                onNext(510, ' bAZ '),
                onNext(530, '    fOo    '),
                onCompleted(570),
                onNext(580, new \Exception()),
                onCompleted(600),
                onError(650, new \Exception())
            ]
        );

        $results = $this->scheduler->startWithDispose(
            function () use ($xs, &$keyInvoked, &$eleInvoked) {
                return $xs->groupByUntil(
                    function ($x) use (&$keyInvoked) {
                        $keyInvoked++;
                        return trim(strtolower($x));
                    },
                    function ($x) use (&$eleInvoked) {
                        $eleInvoked++;
                        return strrev($x);
                    },
                    function (Observable $g) {
                        return $g->skip(2);
                    }
                )
                    ->map(function (GroupedObservable $x) {
                        return $x->getKey();
                    });
            },
            355
        );

        $this->assertMessages(
            [
                onNext(220, 'foo'),
                onNext(270, 'bar'),
                onNext(350, 'baz')
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 355)
            ],
            $xs->getSubscriptions()
        );

        $this->assertEquals(5, $keyInvoked);
        $this->assertEquals(5, $eleInvoked);
    }

    /**
     * @test
     */
    public function groupByUntilOuterKeyThrow()
    {
        $error = new \Exception();

        $keyInvoked = 0;
        $eleInvoked = 0;

        $xs = $this->createHotObservable(
            [
                onNext(90, new \Exception()),
                onNext(110, new \Exception()),
                onNext(130, new \Exception()),
                onNext(220, '  foo'),
                onNext(240, ' FoO '),
                onNext(270, 'baR  '),
                onNext(310, 'foO '),
                onNext(350, ' Baz   '),
                onNext(360, '  qux '),
                onNext(390, '   bar'),
                onNext(420, ' BAR  '),
                onNext(470, 'FOO '),
                onNext(480, 'baz  '),
                onNext(510, ' bAZ '),
                onNext(530, '    fOo    '),
                onCompleted(570),
                onNext(580, new \Exception()),
                onCompleted(600),
                onError(650, new \Exception())
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($xs, &$keyInvoked, &$eleInvoked, $error) {
            return $xs->groupByUntil(
                function ($x) use (&$keyInvoked, $error) {
                    $keyInvoked++;
                    if ($keyInvoked === 10) {
                        throw $error;
                    }
                    return trim(strtolower($x));
                },
                function ($x) use (&$eleInvoked) {
                    $eleInvoked++;
                    return strrev($x);
                },
                function (Observable $g) {
                    return $g->skip(2);
                }
            )->map(function (GroupedObservable $x) {
                return $x->getKey();
            });
        });

        $this->assertMessages([
            onNext(220, 'foo'),
            onNext(270, 'bar'),
            onNext(350, 'baz'),
            onNext(360, 'qux'),
            onNext(470, 'foo'),
            onError(480, $error)
        ], $results->getMessages());

        $this->assertSubscriptions(
            [
                subscribe(200, 480)
            ],
            $xs->getSubscriptions()
        );

        $this->assertEquals(10, $keyInvoked);
        $this->assertEquals(9, $eleInvoked);
    }

    /**
     * @test
     */
    public function groupByUntilOuterEleThrow()
    {
        $error = new \Exception();

        $keyInvoked = 0;
        $eleInvoked = 0;

        $xs = $this->createHotObservable([
            onNext(90, new \Exception()),
            onNext(110, new \Exception()),
            onNext(130, new \Exception()),
            onNext(220, '  foo'),
            onNext(240, ' FoO '),
            onNext(270, 'baR  '),
            onNext(310, 'foO '),
            onNext(350, ' Baz   '),
            onNext(360, '  qux '),
            onNext(390, '   bar'),
            onNext(420, ' BAR  '),
            onNext(470, 'FOO '),
            onNext(480, 'baz  '),
            onNext(510, ' bAZ '),
            onNext(530, '    fOo    '),
            onCompleted(570),
            onNext(580, new \Exception()),
            onCompleted(600),
            onError(650, new \Exception())
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs, &$keyInvoked, &$eleInvoked, $error) {
            return $xs->groupByUntil(function ($x) use (&$keyInvoked) {
                $keyInvoked++;
                return trim(strtolower($x));
            }, function ($x) use (&$eleInvoked, $error) {
                $eleInvoked++;
                if ($eleInvoked === 10) {
                    throw $error;
                }
                return strrev($x);
            }, function (Observable $g) {
                return $g->skip(2);
            })->map(function (GroupedObservable $x) {
                return $x->getKey();
            });
        });

        $this->assertMessages([
            onNext(220, 'foo'),
            onNext(270, 'bar'),
            onNext(350, 'baz'),
            onNext(360, 'qux'),
            onNext(470, 'foo'),
            onError(480, $error)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 480)
        ], $xs->getSubscriptions());

        $this->assertEquals(10, $keyInvoked);
        $this->assertEquals(10, $eleInvoked);
    }

    /**
     * @test
     */
    public function groupByUntilInnerComplete()
    {
        $xs = $this->createHotObservable([
            onNext(90, new \Exception()),
            onNext(110, new \Exception()),
            onNext(130, new \Exception()),
            onNext(220, '  foo'),
            onNext(240, ' FoO '),
            onNext(270, 'baR  '),
            onNext(310, 'foO '),
            onNext(350, ' Baz   '),
            onNext(360, '  qux '),
            onNext(390, '   bar'),
            onNext(420, ' BAR  '),
            onNext(470, 'FOO '),
            onNext(480, 'baz  '),
            onNext(510, ' bAZ '),
            onNext(530, '    fOo    '),
            onCompleted(570),
            onNext(580, new \Exception()),
            onCompleted(600),
            onError(650, new \Exception())
        ]);

        $inners             = [];
        $innerSubscriptions = [];
        $results            = [];
        $outer              = null;
        $outerSubscription  = null;

        $this->scheduler->scheduleAbsolute(TestScheduler::CREATED, function () use ($xs, &$outer) {
            $outer = $xs->groupByUntil(function ($x) {
                return trim(strtolower($x));
            }, function ($x) {
                return strrev($x);
            }, function (Observable $g) {
                return $g->skip(2);
            });
        });

        $this->scheduler->scheduleAbsolute(
            TestScheduler::SUBSCRIBED,
            function () use (&$outer, &$outerSubscription, &$inners, &$results, &$innerSubscriptions) {
                $outerSubscription = $outer->subscribeCallback(function (GroupedObservable $group) use (
                    &$inners,
                    &$results
                ) {
                    $result = $this->scheduler->createObserver();

                    $inners[$group->getKey()]  = $group;
                    $results[$group->getKey()] = $result;

                    $this->scheduler->scheduleRelativeWithState(null, 100, function () use ($group, $result) {
                        $innerSubscriptions[$group->getKey()] = $group->subscribe($result);
                    });
                });
            }
        );

        $this->scheduler->scheduleAbsolute(
            TestScheduler::DISPOSED,
            function () use (&$outerSubscription, &$innerSubscriptions) {
                $outerSubscription->dispose();
                foreach ($innerSubscriptions as $innerSubscription) {
                    $innerSubscription->dispose();
                }
            }
        );

        $this->scheduler->start();

        $this->assertEquals(4, count($inners));

        $this->assertMessages([
            onNext(390, 'rab   '),
            onCompleted(420)
        ], $results['bar']->getMessages());

        $this->assertMessages([
            onNext(480, '  zab'),
            onCompleted(510)
        ], $results['baz']->getMessages());

        $this->assertMessages([
            onCompleted(570)
        ], $results['qux']->getMessages());

        $this->assertMessages([
            onCompleted(570)
        ], $results['foo']->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 570)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function groupByUntilInnerCompleteAll()
    {
        $xs = $this->createHotObservable([
            onNext(90, new \Exception()),
            onNext(110, new \Exception()),
            onNext(130, new \Exception()),
            onNext(220, '  foo'),
            onNext(240, ' FoO '),
            onNext(270, 'baR  '),
            onNext(310, 'foO '),
            onNext(350, ' Baz   '),
            onNext(360, '  qux '),
            onNext(390, '   bar'),
            onNext(420, ' BAR  '),
            onNext(470, 'FOO '),
            onNext(480, 'baz  '),
            onNext(510, ' bAZ '),
            onNext(530, '    fOo    '),
            onCompleted(570),
            onNext(580, new \Exception()),
            onCompleted(600),
            onError(650, new \Exception())
        ]);

        $outer              = null;
        $outerSubscription  = null;
        $inners             = [];
        $innerSubscriptions = [];
        $results            = [];

        $this->scheduler->scheduleAbsolute(TestScheduler::CREATED, function () use ($xs, &$outer) {
            $outer = $xs->groupByUntil(function ($x) {
                return trim(strtolower($x));
            }, function ($x) {
                return strrev($x);
            }, function ($g) {
                return $g->skip(2);
            });
        });

        $this->scheduler->scheduleAbsolute(
            TestScheduler::SUBSCRIBED,
            function () use (&$outerSubscription, &$outer, &$results, &$innerSubscriptions, &$inners) {
                $outerSubscription = $outer->subscribeCallback(function (GroupedObservable $group) use (
                    &$inners,
                    &$results,
                    &$innerSubscriptions
                ) {
                    $result = $this->scheduler->createObserver();

                    $inners[$group->getKey()]  = $group;
                    $results[$group->getKey()] = $result;

                    $innerSubscriptions[$group->getKey()] = $group->subscribe($result);
                });
            }
        );

        $this->scheduler->scheduleAbsolute(
            TestScheduler::DISPOSED,
            function () use (&$outerSubscription, &$innerSubscriptions) {
                $outerSubscription->dispose();
                foreach ($innerSubscriptions as $innerSubscription) {
                    $innerSubscription->dispose();
                }
            }
        );

        $this->scheduler->start();

        $this->assertEquals(4, count($inners));

        $this->assertMessages([
            onNext(270, '  Rab'),
            onNext(390, 'rab   '),
            onNext(420, '  RAB '),
            onCompleted(420)
        ], $results['bar']->getMessages());

        $this->assertMessages([
            onNext(350, '   zaB '),
            onNext(480, '  zab'),
            onNext(510, ' ZAb '),
            onCompleted(510)
        ], $results['baz']->getMessages());

        $this->assertMessages([
            onNext(360, ' xuq  '),
            onCompleted(570)
        ], $results['qux']->getMessages());

        $this->assertMessages([
            onNext(470, ' OOF'),
            onNext(530, '    oOf    '),
            onCompleted(570)
        ], $results['foo']->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 570)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function groupByUntilInnerError()
    {
        $error = new \Exception();

        $xs = $this->createHotObservable([
            onNext(90, new \Exception()),
            onNext(110, new \Exception()),
            onNext(130, new \Exception()),
            onNext(220, '  foo'),
            onNext(240, ' FoO '),
            onNext(270, 'baR  '),
            onNext(310, 'foO '),
            onNext(350, ' Baz   '),
            onNext(360, '  qux '),
            onNext(390, '   bar'),
            onNext(420, ' BAR  '),
            onNext(470, 'FOO '),
            onNext(480, 'baz  '),
            onNext(510, ' bAZ '),
            onNext(530, '    fOo    '),
            onError(570, $error),
            onNext(580, new \Exception()),
            onCompleted(600),
            onError(650, new \Exception())
        ]);

        $outer              = null;
        $outerSubscription  = null;
        $inners             = [];
        $innerSubscriptions = [];
        $results            = [];

        $this->scheduler->scheduleAbsolute(TestScheduler::CREATED, function () use (&$outer, $xs) {
            $outer = $xs->groupByUntil(function ($x) {
                return trim(strtolower($x));
            }, function ($x) {
                return strrev($x);
            }, function ($g) {
                return $g->skip(2);
            });
        });

        $this->scheduler->scheduleAbsolute(
            TestScheduler::SUBSCRIBED,
            function () use (&$outerSubscription, &$outer, &$inners, &$results, &$innerSubscriptions) {
                $outerSubscription = $outer->subscribeCallback(
                    function (GroupedObservable $group) use (
                        &$inners,
                        &$results,
                        &$innerSubscriptions
                    ) {
                        $result = $this->scheduler->createObserver();

                        $inners[$group->getKey()]  = $group;
                        $results[$group->getKey()] = $result;

                        $this->scheduler->scheduleRelativeWithState(
                            null,
                            100,
                            function () use (&$innerSubscriptions, $group, $result) {
                                $innerSubscriptions[$group->getKey()] = $group->subscribe($result);
                            }
                        );
                    },
                    function () {
                    }
                );
            }
        );

        $this->scheduler->scheduleAbsolute(
            TestScheduler::DISPOSED,
            function () use (&$outerSubscription, &$innerSubscriptions) {
                $outerSubscription->dispose();
                foreach ($innerSubscriptions as $innerSubscription) {
                    $innerSubscription->dispose();
                }
            }
        );

        $this->scheduler->start();

        $this->assertEquals(4, count($inners));

        $this->assertMessages([
            onNext(390, 'rab   '),
            onCompleted(420)
        ], $results['bar']->getMessages());

        $this->assertMessages([
            onNext(480, '  zab'),
            onCompleted(510)
        ], $results['baz']->getMessages());

        $this->assertMessages([
            onError(570, $error)
        ], $results['qux']->getMessages());

        $this->assertMessages([
            onError(570, $error)
        ], $results['foo']->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 570)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function groupByUntilInnerDispose()
    {
        $xs = $this->createHotObservable([
            onNext(90, new \Exception()),
            onNext(110, new \Exception()),
            onNext(130, new \Exception()),
            onNext(220, '  foo'),
            onNext(240, ' FoO '),
            onNext(270, 'baR  '),
            onNext(310, 'foO '),
            onNext(350, ' Baz   '),
            onNext(360, '  qux '),
            onNext(390, '   bar'),
            onNext(420, ' BAR  '),
            onNext(470, 'FOO '),
            onNext(480, 'baz  '),
            onNext(510, ' bAZ '),
            onNext(530, '    fOo    '),
            onCompleted(570),
            onNext(580, new \Exception()),
            onCompleted(600),
            onError(650, new \Exception())
        ]);

        $inners             = [];
        $innerSubscriptions = [];
        $results            = [];
        $outer              = null;
        $outerSubscription  = null;

        $this->scheduler->scheduleAbsolute(TestScheduler::CREATED, function () use (&$outer, &$xs) {
            $outer = $xs->groupByUntil(function ($x) {
                return trim(strtolower($x));
            }, function ($x) {
                return strrev($x);
            }, function ($g) {
                return $g->skip(2);
            });
        });

        $this->scheduler->scheduleAbsolute(
            TestScheduler::SUBSCRIBED,
            function () use (&$outerSubscription, &$outer, &$innerSubscriptions, &$results, &$inners) {
                $outerSubscription = $outer->subscribeCallback(function (GroupedObservable $group) use (
                    &$inners,
                    &$results,
                    &$innerSubscriptions
                ) {
                    $result = $this->scheduler->createObserver();

                    $inners[$group->getKey()]  = $group;
                    $results[$group->getKey()] = $result;

                    $innerSubscriptions[$group->getKey()] = $group->subscribe($result);
                });
            }
        );

        $this->scheduler->scheduleAbsolute(400, function () use (&$outerSubscription, &$innerSubscriptions) {
            $outerSubscription->dispose();
            foreach ($innerSubscriptions as $innerSubscription) {
                $innerSubscription->dispose();
            }
        });

        $this->scheduler->start();

        $this->assertEquals(4, count($inners));

        $this->assertMessages([
            onNext(270, '  Rab'),
            onNext(390, 'rab   ')
        ], $results['bar']->getMessages());

        $this->assertMessages([
            onNext(350, '   zaB ')
        ], $results['baz']->getMessages());

        $this->assertMessages([
            onNext(360, ' xuq  ')
        ], $results['qux']->getMessages());

        $this->assertMessages([
            onNext(220, 'oof  '),
            onNext(240, ' OoF '),
            onNext(310, ' Oof'),
            onCompleted(310)
        ], $results['foo']->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 400)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function groupByUntilInnerKeyThrow()
    {
        $error      = new \Exception();
        $keyInvoked = 0;

        $xs = $this->createHotObservable([
            onNext(90, new \Exception()),
            onNext(110, new \Exception()),
            onNext(130, new \Exception()),
            onNext(220, '  foo'),
            onNext(240, ' FoO '),
            onNext(270, 'baR  '),
            onNext(310, 'foO '),
            onNext(350, ' Baz   '),
            onNext(360, '  qux '),
            onNext(390, '   bar'),
            onNext(420, ' BAR  '),
            onNext(470, 'FOO '),
            onNext(480, 'baz  '),
            onNext(510, ' bAZ '),
            onNext(530, '    fOo    '),
            onCompleted(570),
            onNext(580, new \Exception()),
            onCompleted(600),
            onError(650, new \Exception())
        ]);

        $inners             = [];
        $innerSubscriptions = [];
        $results            = [];
        $outer              = null;
        $outerSubscription  = null;

        $this->scheduler->scheduleAbsolute(
            TestScheduler::CREATED,
            function () use (&$outer, $xs, &$keyInvoked, $error) {
                $outer = $xs->groupByUntil(function ($x) use (&$keyInvoked, $error) {
                    $keyInvoked++;
                    if ($keyInvoked === 6) {
                        throw $error;
                    }
                    return trim(strtolower($x));
                }, function ($x) {
                    return strrev($x);
                }, function ($g) {
                    return $g->skip(2);
                });
            }
        );

        $this->scheduler->scheduleAbsolute(
            TestScheduler::SUBSCRIBED,
            function () use (&$outerSubscription, &$outer, &$inners, &$results, &$innerSubscriptions) {
                $outerSubscription = $outer->subscribeCallback(function (GroupedObservable $group) use (
                    &$inners,
                    &$results,
                    &$innerSubscriptions
                ) {
                    $result = $this->scheduler->createObserver();

                    $inners[$group->getKey()]  = $group;
                    $results[$group->getKey()] = $result;

                    $innerSubscriptions[$group->getKey()] = $group->subscribe($result);
                }, function () {
                });
            }
        );

        $this->scheduler->scheduleAbsolute(
            TestScheduler::DISPOSED,
            function () use (&$outerSubscription, &$innerSubscriptions) {
                $outerSubscription->dispose();
                foreach ($innerSubscriptions as $innerSubscription) {
                    $innerSubscription->dispose();
                }
            }
        );

        $this->scheduler->start();

        $this->assertEquals(3, count($inners));

        $this->assertMessages([
            onNext(270, '  Rab'),
            onError(360, $error)
        ], $results['bar']->getMessages());

        $this->assertMessages([
            onNext(350, '   zaB '),
            onError(360, $error)
        ], $results['baz']->getMessages());

        $this->assertMessages([
            onNext(220, 'oof  '),
            onNext(240, ' OoF '),
            onNext(310, ' Oof'),
            onCompleted(310)
        ], $results['foo']->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 360)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function groupByUntilInnerEleThrow()
    {
        $error      = new \Exception();
        $eleInvoked = 0;

        $xs = $this->createHotObservable([
            onNext(90, new \Exception()),
            onNext(110, new \Exception()),
            onNext(130, new \Exception()),
            onNext(220, '  foo'),
            onNext(240, ' FoO '),
            onNext(270, 'baR  '),
            onNext(310, 'foO '),
            onNext(350, ' Baz   '),
            onNext(360, '  qux '),
            onNext(390, '   bar'),
            onNext(420, ' BAR  '),
            onNext(470, 'FOO '),
            onNext(480, 'baz  '),
            onNext(510, ' bAZ '),
            onNext(530, '    fOo    '),
            onCompleted(570),
            onNext(580, new \Exception()),
            onCompleted(600),
            onError(650, new \Exception())
        ]);

        $inners             = [];
        $innerSubscriptions = [];
        $results            = [];
        $outer              = null;
        $outerSubscription  = null;

        $this->scheduler->scheduleAbsolute(
            TestScheduler::CREATED,
            function () use (&$outer, $xs, &$eleInvoked, $error) {
                $outer = $xs->groupByUntil(function ($x) {
                    return trim(strtolower($x));
                }, function ($x) use (&$eleInvoked, $error) {
                    $eleInvoked++;
                    if ($eleInvoked === 6) {
                        throw $error;
                    }
                    return strrev($x);
                }, function ($g) {
                    return $g->skip(2);
                });
            }
        );

        $this->scheduler->scheduleAbsolute(
            TestScheduler::SUBSCRIBED,
            function () use (&$outerSubscription, &$outer, &$inners, &$results) {
                $outerSubscription = $outer->subscribeCallback(function (GroupedObservable $group) use (
                    &$inners,
                    &$results,
                    &$innerSubscriptions
                ) {
                    $result = $this->scheduler->createObserver();

                    $inners[$group->getKey()]  = $group;
                    $results[$group->getKey()] = $result;

                    $innerSubscriptions[$group->getKey()] = $group->subscribe($result);
                }, function () {
                });
            }
        );

        $this->scheduler->scheduleAbsolute(
            TestScheduler::DISPOSED,
            function () use (&$outerSubscription, &$innerSubscriptions) {
                $outerSubscription->dispose();
                foreach ($innerSubscriptions as $innerSubscription) {
                    $innerSubscription->dispose();
                }
            }
        );

        $this->scheduler->start();

        $this->assertEquals(4, count($inners));

        $this->assertMessages([
            onNext(270, '  Rab'),
            onError(360, $error)
        ], $results['bar']->getMessages());

        $this->assertMessages([
            onNext(350, '   zaB '),
            onError(360, $error)
        ], $results['baz']->getMessages());

        $this->assertMessages([
            onError(360, $error)
        ], $results['qux']->getMessages());

        $this->assertMessages([
            onNext(220, 'oof  '),
            onNext(240, ' OoF '),
            onNext(310, ' Oof'),
            onCompleted(310)
        ], $results['foo']->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 360)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function groupByUntilOuterIndependence()
    {
        $xs = $this->createHotObservable([
            onNext(90, new \Exception()),
            onNext(110, new \Exception()),
            onNext(130, new \Exception()),
            onNext(220, '  foo'),
            onNext(240, ' FoO '),
            onNext(270, 'baR  '),
            onNext(310, 'foO '),
            onNext(350, ' Baz   '),
            onNext(360, '  qux '),
            onNext(390, '   bar'),
            onNext(420, ' BAR  '),
            onNext(470, 'FOO '),
            onNext(480, 'baz  '),
            onNext(510, ' bAZ '),
            onNext(530, '    fOo    '),
            onCompleted(570),
            onNext(580, new \Exception()),
            onCompleted(600),
            onError(650, new \Exception())
        ]);

        $inners             = [];
        $innerSubscriptions = [];
        $results            = [];
        $outer              = null;
        $outerSubscription  = null;
        $outerResults       = $this->scheduler->createObserver();

        $this->scheduler->scheduleAbsolute(TestScheduler::CREATED, function () use (&$outer, $xs) {
            $outer = $xs->groupByUntil(function ($x) {
                return trim(strtolower($x));
            }, function ($x) {
                return strrev($x);
            }, function ($g) {
                return $g->skip(2);
            });
        });

        $this->scheduler->scheduleAbsolute(
            TestScheduler::SUBSCRIBED,
            function () use (&$outerSubscription, &$outer, &$outerResults, &$inners, &$results) {
                $outerSubscription = $outer->subscribe(function (GroupedObservable $group) use (
                    &$outerResults,
                    &$inners,
                    &$results,
                    &$innerSubscriptions
                ) {
                    $outerResults->onNext($group->getKey());

                    $result = $this->scheduler->createObserver();

                    $inners[$group->getKey()]  = $group;
                    $results[$group->getKey()] = $result;

                    $innerSubscriptions[$group->getKey()] = $group->subscribe($result);
                }, function ($e) use (&$outerResults) {
                    $outerResults->onError($e);
                }, function () use (&$outerResults) {
                    $outerResults->onCompleted();
                });
            }
        );

        $this->scheduler->scheduleAbsolute(
            TestScheduler::DISPOSED,
            function () use (&$outerSubscription, &$innerSubscriptions) {
                $outerSubscription->dispose();
                foreach ($innerSubscriptions as $innerSubscription) {
                    $innerSubscription->dispose();
                }
            }
        );

        $this->scheduler->scheduleAbsolute(320, function () use (&$outerSubscription) {
            $outerSubscription->dispose();
        });

        $this->scheduler->start();

        $this->assertCount(2, $inners);

        $this->assertMessages([
            onNext(220, 'foo'),
            onNext(270, 'bar')
        ], $outerResults->getMessages());

        $this->assertMessages([
            onNext(220, 'oof  '),
            onNext(240, ' OoF '),
            onNext(310, ' Oof'),
            onCompleted(310)
        ], $results['foo']->getMessages());

        $this->assertMessages([
            onNext(270, '  Rab'),
            onNext(390, 'rab   '),
            onNext(420, '  RAB '),
            onCompleted(420)
        ], $results['bar']->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 420)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function groupByUntilInnerIndependence()
    {
        $xs = $this->createHotObservable([
            onNext(90, new \Exception()),
            onNext(110, new \Exception()),
            onNext(130, new \Exception()),
            onNext(220, '  foo'),
            onNext(240, ' FoO '),
            onNext(270, 'baR  '),
            onNext(310, 'foO '),
            onNext(350, ' Baz   '),
            onNext(360, '  qux '),
            onNext(390, '   bar'),
            onNext(420, ' BAR  '),
            onNext(470, 'FOO '),
            onNext(480, 'baz  '),
            onNext(510, ' bAZ '),
            onNext(530, '    fOo    '),
            onCompleted(570),
            onNext(580, new \Exception()),
            onCompleted(600),
            onError(650, new \Exception())
        ]);

        $inners             = [];
        $innerSubscriptions = [];
        $results            = [];
        $outer              = null;
        $outerSubscription  = null;
        $outerResults       = $this->scheduler->createObserver();

        $this->scheduler->scheduleAbsolute(TestScheduler::CREATED, function () use (&$outer, $xs) {
            $outer = $xs->groupByUntil(function ($x) {
                return trim(strtolower($x));
            }, function ($x) {
                return strrev($x);
            }, function ($g) {
                return $g->skip(2);
            });
        });

        $this->scheduler->scheduleAbsolute(
            TestScheduler::SUBSCRIBED,
            function () use (&$outerSubscription, &$outer, &$outerResults, &$inners, &$results, &$innerSubscriptions) {
                $outerSubscription = $outer->subscribeCallback(function (GroupedObservable $group) use (
                    &$outerResults,
                    &$innerSubscriptions,
                    &$results,
                    &$inners
                ) {
                    $outerResults->onNext($group->getKey());

                    $result = $this->scheduler->createObserver();

                    $inners[$group->getKey()]  = $group;
                    $results[$group->getKey()] = $result;

                    $innerSubscriptions[$group->getKey()] = $group->subscribe($result);
                }, function ($e) use (&$outerResults) {
                    $outerResults->onError($e);
                }, function () use (&$outerResults) {
                    $outerResults->onCompleted();
                });
            }
        );

        $this->scheduler->scheduleAbsolute(
            TestScheduler::DISPOSED,
            function () use (&$outerSubscription, &$innerSubscriptions) {
                $outerSubscription->dispose();
                foreach ($innerSubscriptions as $innerSubscription) {
                    $innerSubscription->dispose();
                }
            }
        );

        $this->scheduler->scheduleAbsolute(320, function () use (&$innerSubscriptions) {
            $innerSubscriptions['foo']->dispose();
        });

        $this->scheduler->start();

        $this->assertEquals(4, count($inners));

        $this->assertMessages([
            onNext(270, '  Rab'),
            onNext(390, 'rab   '),
            onNext(420, '  RAB '),
            onCompleted(420)
        ], $results['bar']->getMessages());

        $this->assertMessages([
            onNext(350, '   zaB '),
            onNext(480, '  zab'),
            onNext(510, ' ZAb '),
            onCompleted(510)
        ], $results['baz']->getMessages());

        $this->assertMessages([
            onNext(360, ' xuq  '),
            onCompleted(570)
        ], $results['qux']->getMessages());

        $this->assertMessages([
            onNext(470, ' OOF'),
            onNext(530, '    oOf    '),
            onCompleted(570)
        ], $results['foo']->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 570)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function groupByUntilInnerMultipleIndependence()
    {
        $xs = $this->createHotObservable([
            onNext(90, new \Exception()),
            onNext(110, new \Exception()),
            onNext(130, new \Exception()),
            onNext(220, '  foo'),
            onNext(240, ' FoO '),
            onNext(270, 'baR  '),
            onNext(310, 'foO '),
            onNext(350, ' Baz   '),
            onNext(360, '  qux '),
            onNext(390, '   bar'),
            onNext(420, ' BAR  '),
            onNext(470, 'FOO '),
            onNext(480, 'baz  '),
            onNext(510, ' bAZ '),
            onNext(530, '    fOo    '),
            onCompleted(570),
            onNext(580, new \Exception()),
            onCompleted(600),
            onError(650, new \Exception())
        ]);

        $results            = [];
        $inners             = [];
        $innerSubscriptions = [];
        $outer              = null;
        $outerSubscription  = null;
        $outerResults       = $this->scheduler->createObserver();

        $this->scheduler->scheduleAbsolute(TestScheduler::CREATED, function () use (&$outer, $xs) {
            $outer = $xs->groupByUntil(function ($x) {
                return trim(strtolower($x));
            }, function ($x) {
                return strrev($x);
            }, function ($g) {
                return $g->skip(2);
            });
        });

        $this->scheduler->scheduleAbsolute(
            TestScheduler::SUBSCRIBED,
            function () use ($outerResults, &$outerSubscription, &$inners, &$results, &$innerSubscriptions, &$outer) {
                $outerSubscription = $outer->subscribeCallback(function (GroupedObservable $group) use (
                    $outerResults,
                    &$inners,
                    &$results,
                    &$innerSubscriptions
                ) {
                    $outerResults->onNext($group->getKey());

                    $result = $this->scheduler->createObserver();

                    $inners[$group->getKey()]  = $group;
                    $results[$group->getKey()] = $result;

                    $innerSubscriptions[$group->getKey()] = $group->subscribe($result);
                }, function ($e) use ($outerResults) {
                    $outerResults->onError($e);
                }, function () use ($outerResults) {
                    $outerResults->onCompleted();
                });
            }
        );

        $this->scheduler->scheduleAbsolute(
            TestScheduler::DISPOSED,
            function () use (&$outerSubscription, &$innerSubscriptions) {
                $outerSubscription->dispose();
                foreach ($innerSubscriptions as $innerSubscription) {
                    $innerSubscription->dispose();
                }
            }
        );

        $this->scheduler->scheduleAbsolute(320, function () use (&$innerSubscriptions) {
            $innerSubscriptions['foo']->dispose();
        });

        $this->scheduler->scheduleAbsolute(280, function () use (&$innerSubscriptions) {
            $innerSubscriptions['bar']->dispose();
        });

        $this->scheduler->scheduleAbsolute(355, function () use (&$innerSubscriptions) {
            $innerSubscriptions['baz']->dispose();
        });

        $this->scheduler->scheduleAbsolute(400, function () use (&$innerSubscriptions) {
            $innerSubscriptions['qux']->dispose();
        });

        $this->scheduler->start();

        $this->assertEquals(4, count($inners));

        $this->assertMessages([
            onNext(270, '  Rab')
        ], $results['bar']->getMessages());

        $this->assertMessages([
            onNext(350, '   zaB ')
        ], $results['baz']->getMessages());

        $this->assertMessages([
            onNext(360, ' xuq  ')
        ], $results['qux']->getMessages());

        $this->assertMessages([
            onNext(470, ' OOF'),
            onNext(530, '    oOf    '),
            onCompleted(570)
        ], $results['foo']->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 570)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function groupByUntilInnerEscapeComplete()
    {
        $xs = $this->createHotObservable([
            onNext(220, '  foo'),
            onNext(240, ' FoO '),
            onNext(310, 'foO '),
            onNext(470, 'FOO '),
            onNext(530, '    fOo    '),
            onCompleted(570)
        ]);

        $results           = $this->scheduler->createObserver();
        $inner             = null;
        $innerSubscription = null;
        $outer             = null;
        $outerSubscription = null;

        $this->scheduler->scheduleAbsolute(TestScheduler::CREATED, function () use (&$outer, $xs) {
            $outer = $xs->groupByUntil(function ($x) {
                return trim(strtolower($x));
            }, function ($x) {
                return strrev($x);
            }, function ($g) {
                return $g->skip(2);
            });
        });

        $this->scheduler->scheduleAbsolute(
            TestScheduler::SUBSCRIBED,
            function () use (&$outerSubscription, &$outer, &$inner) {
                $outerSubscription = $outer->subscribeCallback(function (GroupedObservable $group) use (&$inner) {
                    return $inner = $group;
                });
            }
        );

        $this->scheduler->scheduleAbsolute(600, function () use (&$innerSubscription, &$inner, $results) {
            $innerSubscription = $inner->subscribe($results);
        });

        $this->scheduler->scheduleAbsolute(
            TestScheduler::DISPOSED,
            function () use (&$outerSubscription, &$innerSubscription) {
                $outerSubscription->dispose();
                $innerSubscription->dispose();
            }
        );

        $this->scheduler->start();

        $this->assertSubscriptions([
            subscribe(200, 570)
        ], $xs->getSubscriptions());

        $this->assertMessages([
            onCompleted(600)
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function groupByUntilInnerEscapeError()
    {
        $error = new \Exception();

        $xs = $this->createHotObservable([
            onNext(220, '  foo'),
            onNext(240, ' FoO '),
            onNext(310, 'foO '),
            onNext(470, 'FOO '),
            onNext(530, '    fOo    '),
            onError(570, $error)
        ]);

        $results           = $this->scheduler->createObserver();
        $inner             = null;
        $innerSubscription = null;
        $outer             = null;
        $outerSubscription = null;

        $this->scheduler->scheduleAbsolute(TestScheduler::CREATED, function () use (&$outer, $xs) {
            $outer = $xs->groupByUntil(function ($x) {
                return trim(strtolower($x));
            }, function ($x) {
                return strrev($x);
            }, function ($g) {
                return $g->skip(2);
            });
        });

        $this->scheduler->scheduleAbsolute(
            TestScheduler::SUBSCRIBED,
            function () use (&$outerSubscription, &$outer, &$inner) {
                $outerSubscription = $outer->subscribeCallback(function (GroupedObservable $group) use (&$inner) {
                    $inner = $group;
                }, function () {
                });
            }
        );

        $this->scheduler->scheduleAbsolute(600, function () use (&$innerSubscription, &$inner, $results) {
            $innerSubscription = $inner->subscribe($results);
        });

        $this->scheduler->scheduleAbsolute(
            TestScheduler::DISPOSED,
            function () use (&$innerSubscription, &$outerSubscription) {
                $outerSubscription->dispose();
                $innerSubscription->dispose();
            }
        );

        $this->scheduler->start();

        $this->assertSubscriptions([
            subscribe(200, 570)
        ], $xs->getSubscriptions());

        $this->assertMessages([
            onError(600, $error)
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function groupByUntilInnerEscapeDispose()
    {
        $xs = $this->createHotObservable([
            onNext(220, '  foo'),
            onNext(240, ' FoO '),
            onNext(310, 'foO '),
            onNext(470, 'FOO '),
            onNext(530, '    fOo    '),
            onError(570, new \Exception())
        ]);

        $results           = $this->scheduler->createObserver();
        $inner             = null;
        $innerSubscription = null;
        $outer             = null;
        $outerSubscription = null;

        $this->scheduler->scheduleAbsolute(TestScheduler::CREATED, function () use (&$outer, $xs) {
            $outer = $xs->groupByUntil(function ($x) {
                return trim(strtolower($x));
            }, function ($x) {
                return strrev($x);
            }, function ($g) {
                return $g->skip(2);
            });
        });

        $this->scheduler->scheduleAbsolute(
            TestScheduler::SUBSCRIBED,
            function () use (&$outerSubscription, &$outer, &$inner) {
                $outerSubscription = $outer->subscribeCallback(function ($group) use (&$inner) {
                    $inner = $group;
                });
            }
        );

        $this->scheduler->scheduleAbsolute(290, function () use (&$outerSubscription) {
            $outerSubscription->dispose();
        });

        $this->scheduler->scheduleAbsolute(600, function () use (&$innerSubscription, &$inner, $results) {
            $innerSubscription = $inner->subscribe($results);
        });

        $this->scheduler->scheduleAbsolute(TestScheduler::DISPOSED, function () use (&$outerSubscription) {
            $outerSubscription->dispose();
        });

        $this->scheduler->start();

        $this->assertSubscriptions([
            subscribe(200, 290)
        ], $xs->getSubscriptions());

        $this->assertMessages([], $results->getMessages());
    }

    /**
     * @test
     */
    public function groupByUntilDefault()
    {
        $keyInvoked = 0;
        $eleInvoked = 0;

        $xs = $this->createHotObservable([
            onNext(90, new \Exception()),
            onNext(110, new \Exception()),
            onNext(130, new \Exception()),
            onNext(220, '  foo'),
            onNext(240, ' FoO '),
            onNext(270, 'baR  '),
            onNext(310, 'foO '),
            onNext(350, ' Baz   '),
            onNext(360, '  qux '),
            onNext(390, '   bar'),
            onNext(420, ' BAR  '),
            onNext(470, 'FOO '),
            onNext(480, 'baz  '),
            onNext(510, ' bAZ '),
            onNext(530, '    fOo    '),
            onCompleted(570),
            onNext(580, new \Exception()),
            onCompleted(600),
            onError(650, new \Exception())
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs, &$keyInvoked, &$eleInvoked) {
            return $xs->groupByUntil(function ($x) use (&$keyInvoked) {
                $keyInvoked++;
                return trim(strtolower($x));
            }, function ($x) use (&$eleInvoked) {
                $eleInvoked++;
                return strrev($x);
            }, function ($g) {
                return $g->skip(2);
            })->map(function (GroupedObservable $x) {
                return $x->getKey();
            });
        });

        $this->assertMessages([
            onNext(220, 'foo'),
            onNext(270, 'bar'),
            onNext(350, 'baz'),
            onNext(360, 'qux'),
            onNext(470, 'foo'),
            onCompleted(570)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 570)
        ], $xs->getSubscriptions());

        $this->assertEquals(12, $keyInvoked);
        $this->assertEquals(12, $eleInvoked);
    }

    /**
     * @test
     */
    public function groupByUntilDurationSelectorThrows()
    {
        $error = new \Exception();
        $xs    = $this->createHotObservable([onNext(210, 'foo')]);

        $results = $this->scheduler->startWithCreate(function () use ($xs, $error) {
            return $xs->groupByUntil(function ($x) {
                return $x;
            }, function ($x) {
                return $x;
            }, function () use ($error) {
                throw $error;
            });
        });

        $this->assertMessages([
            onError(210, $error)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 210)
        ], $xs->getSubscriptions());
    }
}
