<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable;
use Rx\Observable\AnonymousObservable;
use Rx\Observable\ErrorObservable;
use Rx\Observable\ReturnObservable;
use Rx\Observer\CallbackObserver;
use Rx\Testing\TestScheduler;

class RetryTest extends FunctionalTestCase
{
    public function testRetryObservableBasic()
    {
        $xs = $this->createColdObservable([
            onNext(100, 1),
            onNext(150, 2),
            onNext(200, 3),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->retry();
        });

        $this->assertMessages(
            [
                onNext(300, 1),
                onNext(350, 2),
                onNext(400, 3),
                onCompleted(450)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 450)
            ],
            $xs->getSubscriptions()
        );
    }

    public function testRetryObservableInfinite()
    {
        $xs = $this->createColdObservable([
            onNext(100, 1),
            onNext(150, 2),
            onNext(200, 3)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->retry();
        });

        $this->assertMessages(
            [
                onNext(300, 1),
                onNext(350, 2),
                onNext(400, 3)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 1000)
            ],
            $xs->getSubscriptions()
        );
    }

    public function testRetryObservableError()
    {
        $error = new \Exception();

        $xs = $this->createColdObservable(
            [
                onNext(100, 1),
                onNext(150, 2),
                onNext(200, 3),
                onError(250, $error)
            ]
        );

        $results = $this->scheduler->startWithDispose(function () use ($xs) {
            return $xs->retry();
        }, 1100);

        $this->assertMessages(
            [
                onNext(300, 1),
                onNext(350, 2),
                onNext(400, 3),
                onNext(550, 1),
                onNext(600, 2),
                onNext(650, 3),
                onNext(800, 1),
                onNext(850, 2),
                onNext(900, 3),
                onNext(1050, 1)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 450),
                subscribe(450, 700),
                subscribe(700, 950),
                subscribe(950, 1100)
            ],
            $xs->getSubscriptions()
        );
    }

    public function testRetryObservableThrows()
    {
        $scheduler1 = new TestScheduler();

        $xs = (new ReturnObservable(1, $scheduler1))->retry();

        $xs->subscribe(
            new CallbackObserver(
                function () {
                    throw new \Exception();
                }
            ));

        $exception = null;
        try {
            $scheduler1->start();
        } catch (\Exception $e) {
            $exception = $e;
        }

        $this->assertEquals(new \Exception(), $exception);

        //    raises(function () {
        //      return scheduler1.start();
        //    });

        $scheduler2 = new TestScheduler();

        $ys = (new ErrorObservable(new \Exception(), $scheduler2))->retry();

        $d = $ys->subscribe(
            new CallbackObserver(
                null,
                function ($err) {
                    throw $err;
                }
            ));

        $scheduler2->scheduleAbsolute(210, function () use ($d) {
            return $d->dispose();
        });

        $scheduler2->start();

        $scheduler3 = new TestScheduler();

        $zs = (new ReturnObservable(1, $scheduler3))->retry();

        $zs->subscribe(
            new CallbackObserver(
                null,
                null,
                function () {
                    throw new \Exception();
                }
            ));

        $exception = null;
        try {
            $scheduler3->start();
        } catch (\Exception $e) {
            $exception = $e;
        }

        $this->assertNotNull($exception);
    }

    public function testRetryObservableRetryCountBasic()
    {
        $error = new \Exception();

        $xs = $this->createColdObservable(
            [
                onNext(5, 1),
                onNext(10, 2),
                onNext(15, 3),
                onError(20, $error)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->retry(3);
        });

        $this->assertMessages(
            [
                onNext(205, 1),
                onNext(210, 2),
                onNext(215, 3),
                onNext(225, 1),
                onNext(230, 2),
                onNext(235, 3),
                onNext(245, 1),
                onNext(250, 2),
                onNext(255, 3),
                onError(260, $error)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 220),
                subscribe(220, 240),
                subscribe(240, 260)
            ],
            $xs->getSubscriptions()
        );
    }

    public function testRetryObservableRetryCountDispose()
    {
        $error = new \Exception();

        $xs = $this->createColdObservable(
            [
                onNext(5, 1),
                onNext(10, 2),
                onNext(15, 3),
                onError(20, $error)
            ]
        );

        $results = $this->scheduler->startWithDispose(function () use ($xs) {
            return $xs->retry(3);
        }, 231);

        $this->assertMessages(
            [
                onNext(205, 1),
                onNext(210, 2),
                onNext(215, 3),
                onNext(225, 1),
                onNext(230, 2)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 220),
                subscribe(220, 231)
            ],
            $xs->getSubscriptions()
        );
    }

    public function testRetryRetryCountDispose()
    {
        $xs = $this->createColdObservable(
            [
                onNext(100, 1),
                onNext(150, 2),
                onNext(200, 3)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->retry(3);
        });

        $this->assertMessages(
            [
                onNext(300, 1),
                onNext(350, 2),
                onNext(400, 3)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 1000)
            ],
            $xs->getSubscriptions()
        );
    }

    public function testRetryObservableCompletes()
    {
        $xs = $this->createColdObservable(
            [
                onNext(100, 1),
                onNext(150, 2),
                onNext(200, 3),
                onCompleted(250)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->retry(3);
        });

        $this->assertMessages(
            [
                onNext(300, 1),
                onNext(350, 2),
                onNext(400, 3),
                onCompleted(450)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 450)
            ],
            $xs->getSubscriptions()
        );
    }

    public function testRetryObservableRetryCountThrows()
    {
        $scheduler1 = new TestScheduler();

        $xs = (new ReturnObservable(1, $scheduler1))->retry(3);

        $xs->subscribe(function () {
            throw new \Exception();
        });

        $exception = null;
        try {
            $scheduler1->start();
        } catch (\Exception $e) {
            $exception = $e;
        }
        $this->assertNotNull($exception);

        $scheduler2 = new TestScheduler();

        $ys = (new ErrorObservable(new \Exception(), $scheduler2))->retry(100);

        $d = $ys->subscribe(null, function ($err) {
            throw $err;
        });

        $scheduler2->scheduleAbsolute(10, function () use ($d) {
            return $d->dispose();
        });

        $scheduler2->start();

        $scheduler3 = new TestScheduler();

        $zs = (new ReturnObservable(1, $scheduler3))->retry(100);

        $zs->subscribe(null, null, function () {
            throw new \Exception();
        });

        $exception = null;
        try {
            $scheduler3->start();
        } catch (\Exception $e) {
            $exception = $e;
        }
        $this->assertNotNull($exception);

        $xss = (new AnonymousObservable(function () {
            throw new \Exception();
        }))->retry(100);

        $exception = null;
        try {
            $xss->subscribe(new CallbackObserver());
        } catch (\Exception $e) {
            $exception = $e;
        }
        $this->assertNotNull($exception);
    }

    public function testWithImmediateSchedulerWithRecursion()
    {
        $completed = false;
        $emitted   = null;

        Observable::range(0, 10)
            ->flatMap(function ($x) use (&$count) {
                if (++$count < 2) {
                    return Observable::error(new \Exception('Something'));
                }
                return Observable::of(42);
            })
            ->retry(3)
            ->take(1)
            ->subscribe(new CallbackObserver(
                function ($x) use (&$emitted) {
                    $emitted = $x;
                },
                null,
                function () use (&$completed) {
                    $completed = true;
                }
            ));

        $this->assertTrue($completed);
        $this->assertEquals(42, $emitted);
    }
}
