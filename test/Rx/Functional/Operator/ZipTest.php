<?php

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable\NeverObservable;
use Rx\Testing\Recorded;

class ZipTest extends FunctionalTestCase
{
    public function testZipNArySymmetric()
    {
        $e0 = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 1),
            onNext(250, 4),
            onCompleted(420)
        ]);

        $e1 = $this->createHotObservable([
            onNext(150, 1),
            onNext(220, 2),
            onNext(240, 5),
            onCompleted(410)
        ]);

        $e2 = $this->createHotObservable([
            onNext(150, 1),
            onNext(230, 3),
            onNext(260, 6),
            onCompleted(400)
        ]);

        $result = $this->scheduler->startWithCreate(function () use ($e0, $e1, $e2) {
            return $e0->zip([$e1, $e2]);
        });

        $this->assertMessages([
            onNext(230, [1, 2, 3]),
            onNext(260, [4, 5, 6]),
            onCompleted(420)
        ],
            $result->getMessages()
        );

        $this->assertSubscriptions([
            subscribe(200, 420)
        ],
            $e0->getSubscriptions());

        $this->assertSubscriptions([
            subscribe(200, 420)
        ],
            $e0->getSubscriptions());

        $this->assertSubscriptions([
            subscribe(200, 420)
        ],
            $e0->getSubscriptions());
    }

    public function testZipNArySymmetricSelector()
    {
        $e0 = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 1),
            onNext(250, 4),
            onCompleted(420)
        ]);

        $e1 = $this->createHotObservable([
            onNext(150, 1),
            onNext(220, 2),
            onNext(240, 5),
            onCompleted(410)
        ]);

        $e2 = $this->createHotObservable([
            onNext(150, 1),
            onNext(230, 3),
            onNext(260, 6),
            onCompleted(400)
        ]);

        $result = $this->scheduler->startWithCreate(function () use ($e0, $e1, $e2) {
            return $e0->zip([$e1, $e2], function ($r0, $r1, $r2) {
                return [$r0, $r1, $r2];
            });
        });

        $this->assertMessages([
            onNext(230, [1, 2, 3]),
            onNext(260, [4, 5, 6]),
            onCompleted(420)
        ],
            $result->getMessages()
        );

        $this->assertSubscriptions([
            subscribe(200, 420)
        ],
            $e0->getSubscriptions());

        $this->assertSubscriptions([
            subscribe(200, 420)
        ],
            $e0->getSubscriptions());

        $this->assertSubscriptions([
            subscribe(200, 420)
        ],
            $e0->getSubscriptions());
    }

    public function testZipNeverNever()
    {
        $o1 = new NeverObservable();
        $o2 = new NeverObservable();

        $results = $this->scheduler->startWithCreate(function () use ($o1, $o2) {
            return $o1->zip([$o2], function ($x, $y) {
                return $x + $y;
            });
        });

        $this->assertMessages([], $results->getMessages());
    }

    public function testZipNeverEmpty()
    {
        $o1 = new NeverObservable();
        $o2 = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(210)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($o1, $o2) {
            return $o1->zip([$o2], function ($x, $y) {
                return $x + $y;
            });
        });

        $this->assertMessages([], $results->getMessages());
    }

    public function testZipEmptyEmpty()
    {
        $o1 = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(210)
        ]);
        $o2 = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(210)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($o1, $o2) {
            return $o1->zip([$o2], function ($x, $y) {
                return $x + $y;
            });
        });

        $this->assertMessages([onCompleted(210)], $results->getMessages());
    }

    public function testZipEmptyNonEmpty()
    {
        $o1 = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(210)
        ]);
        $o2 = $this->createHotObservable([
            onNext(150, 1),
            onNext(215, 2),
            onCompleted(220)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($o1, $o2) {
            return $o1->zip([$o2], function ($x, $y) {
                return $x + $y;
            });
        });

        $this->assertMessages([onCompleted(215)], $results->getMessages());
    }

    public function testZipNonEmptyEmpty()
    {
        $e1 = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(210)
        ]);
        $e2 = $this->createHotObservable([
            onNext(150, 1),
            onNext(215, 2),
            onCompleted(220)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->zip([$e2], function ($x, $y) {
                return $x + $y;
            });
        });

        $this->assertMessages([onCompleted(215)], $results->getMessages());
    }

    public function testZipNeverNonEmpty()
    {
        $e1 = $this->createHotObservable([
            onNext(150, 1),
            onNext(215, 2),
            onCompleted(220)
        ]);
        $e2 = new NeverObservable();

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->zip([$e2], function ($x, $y) {
                return $x + $y;
            });
        });

        $this->assertMessages([], $results->getMessages());
    }

    public function testZipNonEmptyNever()
    {
        $e1 = $this->createHotObservable([
            onNext(150, 1),
            onNext(215, 2),
            onCompleted(220)
        ]);
        $e2 = new NeverObservable();

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->zip([$e2], function ($x, $y) {
                return $x + $y;
            });
        });

        $this->assertMessages([], $results->getMessages());
    }

    public function testZipNonEmptyNonEmpty()
    {
        $e1 = $this->createHotObservable([
            onNext(150, 1),
            onNext(215, 2),
            onCompleted(230)
        ]);
        $e2 = $this->createHotObservable([
            onNext(150, 1),
            onNext(220, 3),
            onCompleted(240)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->zip([$e2], function ($x, $y) {
                return $x + $y;
            });
        });

        $this->assertMessages([
            onNext(220, 2 + 3),
            onCompleted(240)
        ], $results->getMessages());
    }

    public function testZipEmptyError()
    {
        $error = new \Exception();

        $e1 = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(230)
        ]);
        $e2 = $this->createHotObservable([
            onNext(150, 1),
            onError(220, $error)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->zip([$e2], function ($x, $y) {
                return $x + $y;
            });
        });

        $this->assertMessages([
            onError(220, $error)
        ], $results->getMessages());
    }

    public function testZipErrorEmpty()
    {
        $error = new \Exception();

        $e1 = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(230)
        ]);
        $e2 = $this->createHotObservable([
            onNext(150, 1),
            onError(220, $error)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->zip([$e2], function ($x, $y) {
                return $x + $y;
            });
        });

        $this->assertMessages([
            onError(220, $error)
        ], $results->getMessages());
    }

    public function testZipNeverError()
    {
        $error = new \Exception();

        $e1 = new NeverObservable();
        $e2 = $this->createHotObservable([
            onNext(150, 1),
            onError(220, $error)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->zip([$e2], function ($x, $y) {
                return $x + $y;
            });
        });

        $this->assertMessages([
            onError(220, $error)
        ], $results->getMessages());
    }

    public function testZipErrorNever()
    {
        $error = new \Exception();

        $e1 = new NeverObservable();
        $e2 = $this->createHotObservable([
            onNext(150, 1),
            onError(220, $error)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->zip([$e2], function ($x, $y) {
                return $x + $y;
            });
        });

        $this->assertMessages([
            onError(220, $error)
        ], $results->getMessages());
    }

    public function testZipErrorError()
    {
        $error1 = new \Exception("error1");
        $error2 = new \Exception("error2");

        $e1 = $this->createHotObservable([
            onNext(150, 1),
            onError(230, $error1)
        ]);
        $e2 = $this->createHotObservable([
            onNext(150, 1),
            onError(220, $error2)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->zip([$e2], function ($x, $y) {
                return $x + $y;
            });
        });

        $this->assertMessages([
            onError(220, $error2)
        ], $results->getMessages());
    }

    public function testZipSomeError()
    {
        $error = new \Exception();

        $e1 = $this->createHotObservable([
            onNext(150, 1),
            onNext(215, 2),
            onCompleted(230)
        ]);
        $e2 = $this->createHotObservable([
            onNext(150, 1),
            onError(220, $error)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->zip([$e2], function ($x, $y) {
                return $x + $y;
            });
        });

        $this->assertMessages([
            onError(220, $error)
        ], $results->getMessages());
    }

    public function testZipErrorSome()
    {
        $error = new \Exception();

        $e1 = $this->createHotObservable([
            onNext(150, 1),
            onNext(215, 2),
            onCompleted(230)
        ]);
        $e2 = $this->createHotObservable([
            onNext(150, 1),
            onError(220, $error)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->zip([$e2], function ($x, $y) {
                return $x + $y;
            });
        });

        $this->assertMessages([
            onError(220, $error)
        ], $results->getMessages());
    }

    public function testZipSomeDataAsymmetric1()
    {
        $results = [];
        for ($i = 0; $i < 5; $i++) {
            $results[] = onNext(205 + $i * 5, $i);
        }

        /** @var Recorded[] $msgs1 */
        $msgs1 = $results;

        $results = [];
        for ($i = 0; $i < 10; $i++) {
            $results[] = onNext(205 + $i * 8, $i);
        }

        /** @var Recorded[] $msgs2 */
        $msgs2 = $results;

        $len = min(count($msgs1), count($msgs2));

        $e1 = $this->createHotObservable($msgs1);
        $e2 = $this->createHotObservable($msgs2);

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->zip([$e2], function ($x, $y) {
                return $x + $y;
            });
        });

        $this->assertEquals($len, count($results->getMessages()));

        for ($i = 0; $i < $len; $i++) {
            $sum  = $msgs1[$i]->getValue()->getValue() + $msgs2[$i]->getValue()->getValue();
            $time = max($msgs1[$i]->getTime(), $msgs2[$i]->getTime());
            $this->assertEquals($sum, $results->getMessages()[$i]->getValue()->getValue());
            $this->assertEquals($time, $results->getMessages()[$i]->getTime());
        }
    }

    public function testZipSomeDataAsymmetric2()
    {
        $results = [];
        for ($i = 0; $i < 10; $i++) {
            $results[] = onNext(205 + $i * 8, $i);
        }

        /** @var Recorded[] $msgs2 */
        $msgs1 = $results;

        $results = [];
        for ($i = 0; $i < 5; $i++) {
            $results[] = onNext(205 + $i * 5, $i);
        }

        /** @var Recorded[] $msgs1 */
        $msgs2 = $results;

        $len = min(count($msgs1), count($msgs2));

        $e1 = $this->createHotObservable($msgs1);
        $e2 = $this->createHotObservable($msgs2);

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->zip([$e2], function ($x, $y) {
                return $x + $y;
            });
        });

        $this->assertEquals($len, count($results->getMessages()));

        for ($i = 0; $i < $len; $i++) {
            $sum  = $msgs1[$i]->getValue()->getValue() + $msgs2[$i]->getValue()->getValue();
            $time = max($msgs1[$i]->getTime(), $msgs2[$i]->getTime());
            $this->assertEquals($sum, $results->getMessages()[$i]->getValue()->getValue());
            $this->assertEquals($time, $results->getMessages()[$i]->getTime());
        }
    }

    public function testZipSomeDataSymmetric()
    {
        $results = [];
        for ($i = 0; $i < 10; $i++) {
            $results[] = onNext(205 + $i * 5, $i);
        }

        $msgs1 = $results;

        $results = [];
        for ($i = 0; $i < 10; $i++) {
            $results[] = onNext(205 + $i * 8, $i);
        }

        $msgs2 = $results;

        $len = min(count($msgs1), count($msgs2));

        $e1 = $this->createHotObservable($msgs1);
        $e2 = $this->createHotObservable($msgs2);

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->zip([$e2], function ($x, $y) {
                return $x + $y;
            });
        });

        $this->assertEquals($len, count($results->getMessages()));

        for ($i = 0; $i < $len; $i++) {
            $sum  = $msgs1[$i]->getValue()->getValue() + $msgs2[$i]->getValue()->getValue();
            $time = max($msgs1[$i]->getTime(), $msgs2[$i]->getTime());
            $this->assertEquals($sum, $results->getMessages()[$i]->getValue()->getValue());
            $this->assertEquals($time, $results->getMessages()[$i]->getTime());
        }
    }

    public function testZipSelectorThrows()
    {
        $error = new \Exception();

        $e1 = $this->createHotObservable([
            onNext(150, 1),
            onNext(215, 2),
            onNext(225, 4),
            onCompleted(240)
        ]);
        $e2 = $this->createHotObservable([
            onNext(150, 1),
            onNext(220, 3),
            onNext(230, 5),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2, $error) {
            return $e1->zip([$e2], function ($x, $y) use ($error) {
                if ($y === 5) {
                    throw $error;
                }

                return $x + $y;
            });
        });

        $this->assertMessages([
            onNext(220, 2 + 3),
            onError(230, $error)
        ], $results->getMessages());
    }

    public function testZipRightCompletesFirst()
    {
        $o = $this->createHotObservable([
            onNext(150, 1),
            onNext(215, 4),
            onCompleted(225)
        ]);

        $e = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onCompleted(220)
        ]);

        $res = $this->scheduler->startWithCreate(function () use ($o, $e) {
            return $o->zip([$e], function ($x, $y) {
                return $x + $y;
            });
        });

        $this->assertMessages([
            onNext(215, 6),
            onCompleted(225)
        ], $res->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 225)
        ], $e->getSubscriptions());

        $this->assertSubscriptions([
            subscribe(200, 225)
        ], $e->getSubscriptions());
    }
}