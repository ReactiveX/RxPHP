<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable;
use \Exception;

class RepeatWhenTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function repeatWhen_never()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->repeatWhen(function () {
                return Observable::empty();
            });
        });

        $this->assertMessages([
            onCompleted(250)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 250)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function repeatWhen_Observable_never()
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
            return $xs->repeatWhen(function () {
                return Observable::never();
            });
        });

        $this->assertMessages([
            onNext(210, 2),
            onNext(220, 3),
            onNext(230, 4),
            onNext(240, 5)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 250)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function repeatWhen_Observable_empty()
    {
        $xs = $this->createColdObservable([
            onNext(100, 1),
            onNext(150, 2),
            onNext(200, 3),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->repeatWhen(function () {
                return Observable::empty();
            });
        });

        $this->assertMessages([
            onNext(300, 1),
            onNext(350, 2),
            onNext(400, 3),
            onCompleted(450)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 450)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function repeatWhen_next_error()
    {

        $error = new Exception("test");

        $xs = $this->createColdObservable([
            onNext(10, 1),
            onNext(20, 2),
            onError(30, $error),
            onCompleted(40)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs, $error) {
            return $xs->repeatWhen(function (Observable $attempts) use ($error) {
                return $attempts->scan(function ($count) use ($error) {
                    if (++$count === 2) {
                        throw $error;
                    }
                    return $count;
                }, 0);
            });
        });

        $this->assertMessages([
            onNext(210, 1),
            onNext(220, 2),
            onError(230, $error)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 230)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function repeatWhen_Observable_complete()
    {
        $xs = $this->createColdObservable([
            onNext(10, 1),
            onNext(20, 2),
            onCompleted(30)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->repeatWhen(function () {
                return Observable::empty();
            });
        });

        $this->assertMessages([
            onNext(210, 1),
            onNext(220, 2),
            onCompleted(230)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 230)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function repeatWhen_Observable_next_complete()
    {
        $xs = $this->createColdObservable([
            onNext(10, 1),
            onNext(20, 2),
            onCompleted(30)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->repeatWhen(function (Observable $attempts) {
                return $attempts
                    ->scan(function ($count) {
                        return $count + 1;
                    }, 0)
                    ->takeWhile(function ($count) {
                        return $count < 2;
                    });
            });
        });

        $this->assertMessages([
            onNext(210, 1),
            onNext(220, 2),
            onNext(240, 1),
            onNext(250, 2),
            onCompleted(260)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 230),
            subscribe(230, 260)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function repeatWhen_Observable_infinite()
    {
        $xs = $this->createColdObservable([
            onNext(10, 1),
            onNext(20, 2),
            onCompleted(30)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->repeatWhen(function () {
                return Observable::never();
            });
        });

        $this->assertMessages([
            onNext(210, 1),
            onNext(220, 2)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 230)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function repeatWhen_Observable_dispose()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onNext(220, 3),
            onNext(230, 4),
            onNext(240, 5),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithDispose(function () use ($xs) {
            return $xs->repeatWhen(function () {
                return Observable::never();
            });
        }, 225);

        $this->assertMessages([
            onNext(210, 2),
            onNext(220, 3),
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 225)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function repeatWhen_Observable_dispose_second()
    {
        $xs = $this->createColdObservable([
            onNext(10, 1),
            onNext(20, 2),
            onNext(30, 3),
            onNext(40, 4),
            onNext(50, 5),
            onCompleted(60)
        ]);

        $results = $this->scheduler->startWithDispose(function () use ($xs) {
            return $xs->repeatWhen(function ($n) {
                return $n;
            });
        }, 275);

        $this->assertMessages([
            onNext(210, 1),
            onNext(220, 2),
            onNext(230, 3),
            onNext(240, 4),
            onNext(250, 5),
            onNext(270, 1)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 260),
            subscribe(260, 275)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function repeatWhen_Observable_dispose_between()
    {
        $xs = $this->createColdObservable([
            onNext(10, 1),
            onNext(20, 2),
            onNext(30, 3),
            onNext(40, 4),
            onNext(50, 5),
            onCompleted(60)
        ]);

        $results = $this->scheduler->startWithDispose(function () use ($xs) {
            return $xs->repeatWhen(function () {
                return Observable::never();
            });
        }, 275);

        $this->assertMessages([
            onNext(210, 1),
            onNext(220, 2),
            onNext(230, 3),
            onNext(240, 4),
            onNext(250, 5)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 260)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function repeatWhen_notifier_throws()
    {
        $xs = $this->createColdObservable([
            onNext(150, 1),
            onNext(210, 2),
            onNext(220, 3),
            onNext(230, 4),
            onNext(240, 5),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->repeatWhen(function (Observable $attempts) {
                throw new Exception('error');
            });
        });

        $this->assertMessages([
            onError(200, new Exception('error'))
        ], $results->getMessages());

        $this->assertSubscriptions([], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function repeatWhen_notifier_returns_invalid_string()
    {
        $xs = $this->createColdObservable([
            onNext(150, 1),
            onNext(210, 2),
            onNext(220, 3),
            onNext(230, 4),
            onNext(240, 5),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->repeatWhen(function (Observable $attempts) {
                return 'unexpected string';
            });
        });

        $this->assertMessages([
            onError(200, new Exception('error'))
        ], $results->getMessages());

        $this->assertSubscriptions([], $xs->getSubscriptions());
    }
}
