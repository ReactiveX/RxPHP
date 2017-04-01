<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable;
use Rx\Observable\EmptyObservable;

class ConcatTest extends FunctionalTestCase
{
    public function testConcatEmptyEmpty()
    {
        $e1      = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(230)
        ]);
        $e2      = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(250)
        ]);
        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->concat($e2);
        });
        $this->assertMessages([onCompleted(250)], $results->getMessages());
    }

    public function testConcatEmptyNever()
    {
        $e1      = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(230)
        ]);
        $e2      = Observable::never();
        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->concat($e2);
        });
        $this->assertMessages([], $results->getMessages());
    }

    public function testConcatNeverEmpty()
    {
        $e1      = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(230)
        ]);
        $e2      = Observable::never();
        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e2->concat($e1);
        });
        $this->assertMessages([], $results->getMessages());
    }

    public function testConcatNeverNever()
    {
        $e1      = Observable::never();
        $e2      = Observable::never();
        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->concat($e2);
        });
        $this->assertMessages([], $results->getMessages());
    }

    public function testConcatEmptyThrow()
    {
        $e1      = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(230)
        ]);
        $e2      = $this->createHotObservable([
            onNext(150, 1),
            onError(250, new \Exception('error'))
        ]);
        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->concat($e2);
        });
        $this->assertMessages([onError(250, new \Exception('error'))], $results->getMessages());
    }

    public function testConcatThrowEmpty()
    {
        $e1      = $this->createHotObservable([
            onNext(150, 1),
            onError(230, new \Exception('error'))
        ]);
        $e2      = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(250)
        ]);
        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->concat($e2);
        });
        $this->assertMessages([onError(230, new \Exception('error'))], $results->getMessages());
    }

    public function testConcatThrowThrow()
    {
        $e1      = $this->createHotObservable([
            onNext(150, 1),
            onError(230, new \ErrorException())
        ]);
        $e2      = $this->createHotObservable([
            onNext(150, 1),
            onError(250, new \Exception())
        ]);
        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->concat($e2);
        });
        $this->assertMessages([onError(230, new \ErrorException())], $results->getMessages());
    }

    public function testConcatReturnEmpty()
    {
        $e1      = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onCompleted(230)
        ]);
        $e2      = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(250)
        ]);
        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->concat($e2);
        });
        $this->assertMessages([onNext(210, 2), onCompleted(250)], $results->getMessages());
    }

    public function testConcatEmptyReturn()
    {
        $e1      = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(230)
        ]);
        $e2      = $this->createHotObservable([
            onNext(150, 1),
            onNext(240, 2),
            onCompleted(250)
        ]);
        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->concat($e2);
        });
        $this->assertMessages([onNext(240, 2), onCompleted(250)], $results->getMessages());
    }

    public function testConcatReturnNever()
    {
        $e1      = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onCompleted(230)
        ]);
        $e2      = Observable::never();
        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->concat($e2);
        });
        $this->assertMessages([onNext(210, 2)], $results->getMessages());
    }

    public function testConcatNeverReturn()
    {
        $e1      = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onCompleted(230)
        ]);
        $e2      = Observable::never();
        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e2->concat($e1);
        });
        $this->assertMessages([], $results->getMessages());
    }

    public function testConcatReturnReturn()
    {
        $e1      = $this->createHotObservable([
            onNext(150, 1),
            onNext(220, 2),
            onCompleted(230)
        ]);
        $e2      = $this->createHotObservable([
            onNext(150, 1),
            onNext(240, 3),
            onCompleted(250)
        ]);
        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->concat($e2);
        });
        $this->assertMessages([onNext(220, 2), onNext(240, 3), onCompleted(250)], $results->getMessages());
    }

    public function testConcatThrowReturn()
    {
        $e1      = $this->createHotObservable([
            onNext(150, 1),
            onError(220, new \Exception())
        ]);
        $e2      = $this->createHotObservable([
            onNext(150, 1),
            onNext(240, 3),
            onCompleted(250)
        ]);
        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->concat($e2);
        });
        $this->assertMessages([onError(220, new \Exception())], $results->getMessages());
    }

    public function testConcatReturnThrow()
    {
        $e1      = $this->createHotObservable([
            onNext(150, 1),
            onNext(220, 2),
            onCompleted(230)
        ]);
        $e2      = $this->createHotObservable([
            onNext(150, 1),
            onError(250, new \Exception())
        ]);
        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->concat($e2);
        });
        $this->assertMessages([onNext(220, 2), onError(250, new \Exception())], $results->getMessages());
    }

    public function testConcatSomeDataOnBothSides()
    {
        $e1      = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onNext(220, 3),
            onCompleted(225)
        ]);
        $e2      = $this->createHotObservable([
            onNext(150, 1),
            onNext(230, 4),
            onNext(240, 5),
            onCompleted(250)
        ]);
        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->concat($e2);
        });
        $this->assertMessages([
            onNext(210, 2),
            onNext(220, 3),
            onNext(230, 4),
            onNext(240, 5),
            onCompleted(250)
        ],
            $results->getMessages());
    }

    public function testConcatAsArguments()
    {
        $xs1     = $this->createColdObservable([
            onNext(10, 1),
            onNext(20, 2),
            onNext(30, 3),
            onCompleted(40)
        ]);
        $xs2     = $this->createColdObservable([
            onNext(10, 4),
            onNext(20, 5),
            onCompleted(30)
        ]);
        $xs3     = $this->createColdObservable([
            onNext(10, 6),
            onNext(20, 7),
            onNext(30, 8),
            onNext(40, 9),
            onCompleted(50)
        ]);
        $results = $this->scheduler->startWithCreate(function () use ($xs1, $xs2, $xs3) {
            return (new EmptyObservable($this->scheduler))->concat($xs1)->concat($xs2)->concat($xs3);
        });

        // Note: these tests differ from the RxJS tests that they were based on because RxJS was
        // explicitly using the immediate scheduler on subscribe internally. When we pass the
        // proper scheduler in, the subscription gets scheduled which requires an extra tick.
        $this->assertMessages(
            [
                onNext(211, 1),
                onNext(221, 2),
                onNext(231, 3),
                onNext(251, 4),
                onNext(261, 5),
                onNext(281, 6),
                onNext(291, 7),
                onNext(301, 8),
                onNext(311, 9),
                onCompleted(321)
            ],
            $results->getMessages()
        );
        $this->assertSubscriptions([subscribe(201, 241)], $xs1->getSubscriptions());
        $this->assertSubscriptions([subscribe(241, 271)], $xs2->getSubscriptions());
        $this->assertSubscriptions([subscribe(271, 321)], $xs3->getSubscriptions());

    }

    public function testConcatAll()
    {

        $sources = Observable::fromArray([
            Observable::of(0),
            Observable::of(1),
            Observable::of(2),
            Observable::of(3),
        ]);

        $res       = [];
        $completed = false;

        $sources->concatAll()->subscribe(
            function ($x) use (&$res) {
                $res[] = $x;
            },
            function ($e) {
                $this->fail();
            },
            function () use (&$completed) {
                $completed = true;
            }
        );

        $this->assertEquals([0, 1, 2, 3], $res);
        $this->assertTrue($completed);
    }


    public function testConcatAllError()
    {

        $sources = Observable::fromArray([
            Observable::of(0),
            Observable::error(new \Exception()),
            Observable::of(2),
            Observable::of(3),
        ]);

        $res       = [];
        $error     = false;
        $completed = false;

        $sources->concatAll()->subscribe(
            function ($x) use (&$res) {
                $res[] = $x;
            },
            function ($e) use (&$res, &$error) {
                $this->assertEquals([0], $res);
                $error = true;
            },
            function () use (&$completed) {
                $completed = true;
            }
        );

        $this->assertTrue($error);
        $this->assertFalse($completed);

    }

    public function testConcatDispose()
    {
        $o1 = $this->createHotObservable([
            onNext(250, 1),
            onNext(300, 2),
            onCompleted(325)
        ]);

        $o2 = $this->createHotObservable([
            onNext(350, 3),
            onNext(400, 4),
            onCompleted(450)
        ]);

        $results = $this->scheduler->startWithDispose(function () use ($o1, $o2) {
            return $o1->concat($o2);
        }, 375);

        $this->assertMessages([
            onNext(250, 1),
            onNext(300, 2),
            onNext(350, 3),
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 325)
        ], $o1->getSubscriptions());

        $this->assertSubscriptions([
            subscribe(325, 375)
        ], $o2->getSubscriptions());
    }
}