<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable\ArrayObservable;
use Rx\Observable\NeverObservable;
use Rx\Observer\CallbackObserver;
use Rx\Scheduler\ImmediateScheduler;

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

        $this->assertMessages(
            [
                onNext(230, [1, 2, 3]),
                onNext(260, [4, 5, 6]),
                onCompleted(420)
            ],
            $result->getMessages()
        );

        $this->assertSubscriptions([subscribe(200, 420)], $e0->getSubscriptions());

        $this->assertSubscriptions([subscribe(200, 420)], $e0->getSubscriptions());

        $this->assertSubscriptions([subscribe(200, 420)], $e0->getSubscriptions());
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
                return [$r2, $r1, $r0];
            });
        });

        $this->assertMessages(
            [
                onNext(230, [3, 2, 1]),
                onNext(260, [6, 5, 4]),
                onCompleted(420)
            ],
            $result->getMessages()
        );

        $this->assertSubscriptions([subscribe(200, 420)], $e0->getSubscriptions());

        $this->assertSubscriptions([subscribe(200, 420)], $e0->getSubscriptions());

        $this->assertSubscriptions([subscribe(200, 420)], $e0->getSubscriptions());
    }

    public function testZipNeverNever()
    {
        $o1 = new NeverObservable();
        $o2 = new NeverObservable();

        $results = $this->scheduler->startWithCreate(function () use ($o1, $o2) {
            return $o1->zip([$o2], [$this,'add']);
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
            return $o1->zip([$o2], [$this,'add']);
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
            return $o1->zip([$o2], [$this,'add']);
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
            return $o1->zip([$o2], [$this,'add']);
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
            return $e2->zip([$e1], [$this,'add']);
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
            return $e2->zip([$e1], [$this,'add']);
        });

        $this->assertMessages([], $results->getMessages());
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
            return $e1->zip([$e2], [$this,'add']);
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
            return $e1->zip([$e2], [$this,'add']);
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
            return $e1->zip([$e2], [$this,'add']);
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
            return $e1->zip([$e2], [$this,'add']);
        });

        $this->assertMessages([
            onError(220, $error)
        ], $results->getMessages());
    }

    public function testZipSomeDataAsymmetric()
    {
        $msgs1 = [
            onNext(205, 0),
            onNext(210, 1),
            onNext(215, 2),
        ];

        $msgs2 = [
            onNext(205, 0),
            onNext(213, 1),
            onNext(221, 2),
            onNext(229, 3),
            onNext(237, 4),
        ];

        $e1 = $this->createHotObservable($msgs1);
        $e2 = $this->createHotObservable($msgs2);

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->zip([$e2], [$this,'add']);
        });

        $this->assertMessages([
            onNext(205, 0),
            onNext(213, 2),
            onNext(221, 4),
        ], $results->getMessages());
    }

    public function testZipSomeDataSymmetric()
    {
        $msgs1 = [
            onNext(205, 0),
            onNext(210, 1),
            onNext(215, 2),
        ];

        $msgs2 = [
            onNext(205, 0),
            onNext(213, 1),
            onNext(221, 2),
        ];

        $e1 = $this->createHotObservable($msgs1);
        $e2 = $this->createHotObservable($msgs2);

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->zip([$e2], [$this,'add']);
        });

        $this->assertMessages([
            onNext(205, 0),
            onNext(213, 2),
            onNext(221, 4),
        ], $results->getMessages());
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
            return $o->zip([$e], [$this,'add']);
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

    public function add($x, $y)
    {
        return $x + $y;
    }

    public function testZipWithImmediateScheduler()
    {
        $scheduler = new ImmediateScheduler();

        $o = new ArrayObservable(range(0, 4), $scheduler);

        $source = $o
            ->zip([
                $o->skip(1),
                $o->skip(2)
            ]);

        $result = null;

        $source->toArray()->subscribe(new CallbackObserver(
            function ($x) use (&$result) {
                $result = $x;
            }
        ));

        $this->assertEquals(
            [
                [0, 1, 2],
                [1, 2, 3],
                [2, 3, 4]
            ],
            $result
        );

        $result = null;

        $source->count()->subscribe(new CallbackObserver(
            function ($x) use (&$result) {
                $result = $x;
            }
        ));

        $this->assertEquals(3, $result);
    }
}