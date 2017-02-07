<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable;

class RetryWhenTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function retryWhen_never()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->retryWhen(function () {
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
    public function retryWhen_Observable_never()
    {
        $error = new \Exception();

        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onNext(220, 3),
            onNext(230, 4),
            onNext(240, 5),
            onError(250, $error)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->retryWhen(function () {
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
    public function retryWhen_never_completed()
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
            return $xs->retryWhen(function () {
                return Observable::never();
            });
        });

        $this->assertMessages([
            onNext(210, 2),
            onNext(220, 3),
            onNext(230, 4),
            onNext(240, 5),
            onCompleted(250)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 250)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function retryWhen_Observable_Empty()
    {
        $xs = $this->createColdObservable([
            onNext(100, 1),
            onNext(150, 2),
            onNext(200, 3),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->retryWhen(function () {
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
    public function retryWhen_Observable_Next_Error()
    {
        $error = new \Exception();

        $xs = $this->createColdObservable([
            onNext(10, 1),
            onNext(20, 2),
            onError(30, $error),
            onCompleted(40)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs, $error) {
            return $xs->retryWhen(function (Observable $attempts) {
                return $attempts->scan(function ($count, $error) {
                    if (++$count === 2) {
                        throw $error;
                    }
                    return $count;
                }, 0); // returning any nexting observable should cause a continue
            });
        });

        $this->assertMessages([
            onNext(210, 1),
            onNext(220, 2),
            onNext(240, 1),
            onNext(250, 2),
            onError(260, $error)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 230),
            subscribe(230, 260)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function retryWhen_Observable_complete()
    {
        $error = new \Exception();

        $xs = $this->createColdObservable([
            onNext(10, 1),
            onNext(20, 2),
            onError(30, $error),
            onCompleted(40)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs, $error) {
            return $xs->retryWhen(function (Observable $attempts) {
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
    public function retryWhen_Observable_next_complete()
    {
        $error = new \Exception();

        $xs = $this->createColdObservable([
            onNext(10, 1),
            onNext(20, 2),
            onError(30, $error),
            onCompleted(40)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs, $error) {
            return $xs->retryWhen(function (Observable $attempts) {
                return $attempts->scan(function ($count, $error) {
                    return $count + 1;
                }, 0)->takeWhile(function ($count) {
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
    public function retryWhen_Observable_infinite()
    {
        $error = new \Exception();

        $xs = $this->createColdObservable([
            onNext(10, 1),
            onNext(20, 2),
            onError(30, $error),
            onCompleted(40)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs, $error) {
            return $xs->retryWhen(function () {
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

    public function testRetryWhenDispose()
    {
        $xs = $this->createColdObservable([
            onNext(10, 1),
            onNext(20, 2),
            onNext(30, 3),
            onNext(40, 4),
            onCompleted(50)
        ]);

        $results = $this->scheduler->startWithDispose(function () use ($xs) {
            return $xs->map(function ($x) {
                if ($x > 2) {
                    throw new \Exception;
                }
                return $x;
            })->retryWhen(function (Observable $attempts) {
                return $attempts;
            });
        }, 285);

        $this->assertMessages([
            onNext(210, 1),
            onNext(220, 2),
            onNext(240, 1),
            onNext(250, 2),
            onNext(270, 1),
            onNext(280, 2),
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 230),
            subscribe(230, 260),
            subscribe(260, 285)
        ], $xs->getSubscriptions());
    }

    public function testRetryWhenDisposeBetweenSourceSubscriptions()
    {
        $xs = $this->createColdObservable([
            onNext(10, 1),
            onNext(20, 2),
            onNext(30, 3),
            onNext(40, 4),
            onCompleted(50)
        ]);

        $results = $this->scheduler->startWithDispose(function () use ($xs) {
            return $xs->map(function ($x) {
                if ($x > 2) {
                    throw new \Exception;
                }
                return $x;
            })->retryWhen(function ($attempts) {
                return Observable::never();
            });
        }, 285);

        $this->assertMessages([
            onNext(210, 1),
            onNext(220, 2),
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 230)
        ], $xs->getSubscriptions());
    }

    public function testRetryWhenInnerEmitsBeforeOuterError()
    {
        $xs = $this->createColdObservable([
            onNext(10, 1),
            onNext(20, 2),
            onNext(30, 3),
            onNext(40, 4),
            onCompleted(50)
        ]);

        $results = $this->scheduler->startWithDispose(function () use ($xs) {
            return $xs->retryWhen(function () {
                return Observable::of(1);
            });
        }, 285);

        $this->assertMessages([
            onNext(210, 1),
            onNext(220, 2),
            onNext(230, 3),
            onNext(240, 4),
            onCompleted(250)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 250)
        ], $xs->getSubscriptions());
    }

    public function testRetryWhenSelectorThrows()
    {
        $error = new \Exception();

        $xs = $this->createColdObservable([
            onNext(10, 1),
            onNext(20, 2),
            onNext(30, 3),
            onNext(40, 4),
            onCompleted(50)
        ]);

        $results = $this->scheduler->startWithDispose(function () use ($xs, $error) {
            return $xs->retryWhen(function () use ($error) {
                throw $error;
            });
        }, 285);

        $this->assertMessages([
            onError(200, $error)
        ], $results->getMessages());

        $this->assertSubscriptions([], $xs->getSubscriptions());
    }

    public function testRetryWhenSelectorReturnsInvalidString()
    {
        $error = new \Exception();

        $xs = $this->createColdObservable([
            onNext(10, 1),
            onNext(20, 2),
            onNext(30, 3),
            onNext(40, 4),
            onCompleted(50)
        ]);

        $results = $this->scheduler->startWithDispose(function () use ($xs, $error) {
            return $xs->retryWhen(function () use ($error) {
                return 'unexpected string';
            });
        }, 285);

        $this->assertMessages([
            onError(200, $error)
        ], $results->getMessages());

        $this->assertSubscriptions([], $xs->getSubscriptions());
    }
}
