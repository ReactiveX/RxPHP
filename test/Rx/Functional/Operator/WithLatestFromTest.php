<?php

declare(strict_types=1);

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable\NeverObservable;

class WithLatestFromTest extends FunctionalTestCase
{
    public function add($a, $b)
    {
        return $a + $b;
    }

    /**
     * @test
     */
    public function withLatestFrom_never_never()
    {
        $e1 = new NeverObservable();
        $e2 = new NeverObservable();

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->withLatestFrom([$e2], [$this, 'add']);
        });

        $this->assertMessages([], $results->getMessages());
    }

    /**
     * @test
     */
    public function withLatestFrom_never_empty()
    {
        $e1 = new NeverObservable();
        $e2 = $this->createHotObservable(
            [
                onNext(150, 1),
                onCompleted(210)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->withLatestFrom([$e2], [$this, 'add']);
        });

        $this->assertMessages([], $results->getMessages());
    }

    /**
     * @test
     */
    public function withLatestFrom_empty_never()
    {
        $e1 = new NeverObservable();
        $e2 = $this->createHotObservable(
            [
                onNext(150, 1),
                onCompleted(210)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e2->withLatestFrom([$e1], [$this, 'add']);
        });

        $this->assertMessages([onCompleted(210)], $results->getMessages());
    }

    /**
     * @test
     */
    public function withLatestFrom_empty_empty()
    {
        $e1 = $this->createHotObservable(
            [
                onNext(150, 1),
                onCompleted(210)
            ]
        );

        $e2 = $this->createHotObservable(
            [
                onNext(150, 1),
                onCompleted(210)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e2->withLatestFrom([$e1], [$this, 'add']);
        });

        $this->assertMessages([onCompleted(210)], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 210)
        ], $e1->getSubscriptions());

        $this->assertSubscriptions([
            subscribe(200, 210)
        ], $e2->getSubscriptions());
    }


    /**
     * @test
     */
    public function withLatestFrom_empty_return()
    {
        $e1 = $this->createHotObservable(
            [
                onNext(150, 1),
                onCompleted(210)
            ]
        );

        $e2 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(215, 2),
                onCompleted(220)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->withLatestFrom([$e2], [$this, 'add']);
        });

        $this->assertMessages([onCompleted(210)], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 210)
        ], $e1->getSubscriptions());

        $this->assertSubscriptions([
            subscribe(200, 210)
        ], $e2->getSubscriptions());
    }

    /**
     * @test
     */
    public function withLatestFrom_return_empty()
    {
        $e1 = $this->createHotObservable(
            [
                onNext(150, 1),
                onCompleted(210)
            ]
        );

        $e2 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(215, 2),
                onCompleted(220)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e2->withLatestFrom([$e1], [$this, 'add']);
        });

        $this->assertMessages([onCompleted(220)], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 220)
        ], $e1->getSubscriptions());

        $this->assertSubscriptions([
            subscribe(200, 220)
        ], $e1->getSubscriptions());
    }

    /**
     * @test
     */
    public function withLatestFrom_never_return()
    {
        $e1 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(215, 2),
                onCompleted(220)
            ]
        );

        $e2 = new NeverObservable();

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->withLatestFrom([$e2], [$this, 'add']);
        });

        $this->assertMessages([onCompleted(220)], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 220)
        ], $e1->getSubscriptions());
    }

    /**
     * @test
     */
    public function withLatestFrom_return_never()
    {
        $e1 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(215, 2),
                onCompleted(220)
            ]
        );

        $e2 = new NeverObservable();

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e2->withLatestFrom([$e1], [$this, 'add']);
        });

        $this->assertMessages([], $results->getMessages());
    }

    /**
     * @test
     */
    public function withLatestFrom_return_return()
    {
        $e1 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(220, 2),
                onCompleted(230)
            ]
        );

        $e2 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(215, 3),
                onCompleted(240)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->withLatestFrom([$e2], [$this, 'add']);
        });

        $this->assertMessages(
            [
                onNext(220, 2 + 3),
                onCompleted(230)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions([
            subscribe(200, 230)
        ], $e1->getSubscriptions());

        $this->assertSubscriptions([
            subscribe(200, 230)
        ], $e2->getSubscriptions());
    }

    /**
     * @test
     */
    public function withLatestFrom_return_return_no_selector()
    {
        $e1 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(220, 2),
                onCompleted(230)
            ]
        );

        $e2 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(215, 3),
                onCompleted(240)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->withLatestFrom([$e2]);
        });

        $this->assertMessages(
            [
                onNext(220, [2, 3]),
                onCompleted(230)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions([
            subscribe(200, 230)
        ], $e1->getSubscriptions());

        $this->assertSubscriptions([
            subscribe(200, 230)
        ], $e2->getSubscriptions());
    }

    /**
     * @test
     */
    public function withLatestFrom_empty_error()
    {
        $error = new \Exception();

        $e1 = $this->createHotObservable(
            [
                onNext(150, 1),
                onCompleted(230)
            ]
        );

        $e2 = $this->createHotObservable(
            [
                onNext(150, 1),
                onError(220, $error)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->withLatestFrom([$e2], [$this, 'add']);
        });

        $this->assertMessages(
            [
                onError(220, $error)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions([
            subscribe(200, 220)
        ], $e1->getSubscriptions());

        $this->assertSubscriptions([
            subscribe(200, 220)
        ], $e2->getSubscriptions());
    }

    /**
     * @test
     */
    public function withLatestFrom_error_empty()
    {
        $error = new \Exception();

        $e1 = $this->createHotObservable(
            [
                onNext(150, 1),
                onCompleted(230)
            ]
        );

        $e2 = $this->createHotObservable(
            [
                onNext(150, 1),
                onError(220, $error)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e2->withLatestFrom([$e1], [$this, 'add']);
        });

        $this->assertMessages(
            [
                onError(220, $error)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions([
            subscribe(200, 220)
        ], $e1->getSubscriptions());

        $this->assertSubscriptions([
            subscribe(200, 220)
        ], $e2->getSubscriptions());
    }

    /**
     * @test
     */
    public function withLatestFrom_return_throw()
    {
        $error = new \Exception();

        $e1 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(210, 2),
                onCompleted(230)
            ]
        );

        $e2 = $this->createHotObservable(
            [
                onNext(150, 1),
                onError(220, $error)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->withLatestFrom([$e2], [$this, 'add']);
        });

        $this->assertMessages(
            [
                onError(220, $error)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions([
            subscribe(200, 220)
        ], $e1->getSubscriptions());

        $this->assertSubscriptions([
            subscribe(200, 220)
        ], $e2->getSubscriptions());
    }

    /**
     * @test
     */
    public function withLatestFrom_throw_return()
    {
        $error = new \Exception();

        $e1 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(210, 2),
                onCompleted(230)
            ]
        );

        $e2 = $this->createHotObservable(
            [
                onNext(150, 1),
                onError(220, $error)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e2->withLatestFrom([$e1], [$this, 'add']);
        });

        $this->assertMessages(
            [
                onError(220, $error)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions([
            subscribe(200, 220)
        ], $e1->getSubscriptions());

        $this->assertSubscriptions([
            subscribe(200, 220)
        ], $e2->getSubscriptions());
    }

    /**
     * @test
     */
    public function withLatestFrom_throw_throw()
    {
        $error1 = new \Exception('first');
        $error2 = new \Exception('second');

        $e1 = $this->createHotObservable(
            [
                onNext(150, 1),
                onError(220, $error1)
            ]
        );

        $e2 = $this->createHotObservable(
            [
                onNext(150, 1),
                onError(220, $error2)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->withLatestFrom([$e2], [$this, 'add']);
        });

        $this->assertMessages(
            [
                onError(220, $error1)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions([
            subscribe(200, 220)
        ], $e1->getSubscriptions());

        $this->assertSubscriptions([
            subscribe(200, 220)
        ], $e2->getSubscriptions());
    }

    /**
     * @test
     */
    public function withLatestFrom_error_throw()
    {
        $error1 = new \Exception();
        $error2 = new \Exception();

        $e1 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(210, 2),
                onError(220, $error1)
            ]
        );

        $e2 = $this->createHotObservable(
            [
                onNext(150, 1),
                onError(220, $error2)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->withLatestFrom([$e2], [$this, 'add']);
        });

        $this->assertMessages(
            [
                onError(220, $error1)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions([
            subscribe(200, 220)
        ], $e1->getSubscriptions());

        $this->assertSubscriptions([
            subscribe(200, 220)
        ], $e2->getSubscriptions());
    }

    /**
     * @test
     */
    public function withLatestFrom_throw_error()
    {
        $error1 = new \Exception();
        $error2 = new \Exception();

        $e1 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(210, 2),
                onError(220, $error1)
            ]
        );

        $e2 = $this->createHotObservable(
            [
                onNext(150, 1),
                onError(220, $error2)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e2->withLatestFrom([$e1], [$this, 'add']);
        });

        $this->assertMessages(
            [
                onError(220, $error1)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions([
            subscribe(200, 220)
        ], $e1->getSubscriptions());

        $this->assertSubscriptions([
            subscribe(200, 220)
        ], $e2->getSubscriptions());
    }

    /**
     * @test
     */
    public function withLatestFrom_never_throw()
    {
        $error = new \Exception();

        $e1 = new NeverObservable();

        $e2 = $this->createHotObservable(
            [
                onNext(150, 1),
                onError(220, $error)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->withLatestFrom([$e2], [$this, 'add']);
        });

        $this->assertMessages(
            [
                onError(220, $error)
            ],
            $results->getMessages()
        );
    }

    /**
     * @test
     */
    public function withLatestFrom_throw_never()
    {
        $error = new \Exception();

        $e1 = new NeverObservable();

        $e2 = $this->createHotObservable(
            [
                onNext(150, 1),
                onError(220, $error)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e2->withLatestFrom([$e1], [$this, 'add']);
        });

        $this->assertMessages(
            [
                onError(220, $error)
            ],
            $results->getMessages()
        );
    }

    /**
     * @test
     */
    public function withLatestFrom_some_throw()
    {
        $error = new \Exception();

        $e1 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(215, 2),
                onCompleted(230)
            ]
        );

        $e2 = $this->createHotObservable(
            [
                onNext(150, 1),
                onError(220, $error)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->withLatestFrom([$e2], [$this, 'add']);
        });

        $this->assertMessages(
            [
                onError(220, $error)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions([
            subscribe(200, 220)
        ], $e1->getSubscriptions());

        $this->assertSubscriptions([
            subscribe(200, 220)
        ], $e2->getSubscriptions());
    }

    /**
     * @test
     */
    public function withLatestFrom_throw_some()
    {
        $error = new \Exception();

        $e1 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(215, 2),
                onCompleted(230)
            ]
        );

        $e2 = $this->createHotObservable(
            [
                onNext(150, 1),
                onError(220, $error)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e2->withLatestFrom([$e1], [$this, 'add']);
        });

        $this->assertMessages(
            [
                onError(220, $error)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions([
            subscribe(200, 220)
        ], $e1->getSubscriptions());

        $this->assertSubscriptions([
            subscribe(200, 220)
        ], $e2->getSubscriptions());
    }

    /**
     * @test
     */
    public function withLatestFrom_throw_after_complete_left()
    {
        $error = new \Exception();

        $e1 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(215, 2),
                onCompleted(220)
            ]
        );

        $e2 = $this->createHotObservable(
            [
                onNext(150, 1),
                onError(230, $error)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->withLatestFrom([$e2], [$this, 'add']);
        });

        $this->assertMessages(
            [
                onCompleted(220)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions([
            subscribe(200, 220)
        ], $e1->getSubscriptions());

        $this->assertSubscriptions([
            subscribe(200, 220)
        ], $e2->getSubscriptions());
    }

    /**
     * @test
     */
    public function withLatestFrom_throw_after_complete_right()
    {
        $error = new \Exception();

        $e1 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(215, 2),
                onCompleted(220)
            ]
        );

        $e2 = $this->createHotObservable(
            [
                onNext(150, 1),
                onError(230, $error)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e2->withLatestFrom([$e1], [$this, 'add']);
        });

        $this->assertMessages(
            [
                onError(230, $error)
            ],
            $results->getMessages()
        );
    }

    /**
     * @test
     */
    public function withLatestFrom_interleaved_with_tail()
    {

        $e1 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(220, 3),
                onNext(230, 5),
                onNext(235, 6),
                onNext(240, 7),
                onCompleted(250)
            ]
        );

        $e2 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(215, 2),
                onNext(225, 4),
                onCompleted(230)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->withLatestFrom([$e2], [$this, 'add']);
        });

        $this->assertMessages(
            [
                onNext(220, 3 + 2),
                onNext(230, 5 + 4),
                onNext(235, 6 + 4),
                onNext(240, 7 + 4),
                onCompleted(250)

            ],
            $results->getMessages()
        );

        $this->assertSubscriptions([
            subscribe(200, 250)
        ], $e1->getSubscriptions());
    }

    /**
     * @test
     */
    public function withLatestFrom_consecutive()
    {
        $e1 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(215, 2),
                onNext(235, 4),
                onCompleted(240)
            ]
        );

        $e2 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(225, 6),
                onNext(240, 7),
                onCompleted(250)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->withLatestFrom([$e2], [$this, 'add']);
        });

        $this->assertMessages(
            [
                onNext(235, 4 + 6),
                onCompleted(240)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions([
            subscribe(200, 240)
        ], $e1->getSubscriptions());

        $this->assertSubscriptions([
            subscribe(200, 240)
        ], $e2->getSubscriptions());
    }

    /**
     * @test
     */
    public function withLatestFrom_consecutive_array()
    {
        $e1 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(215, 2),
                onNext(235, 4),
                onCompleted(240)
            ]
        );

        $e2 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(225, 6),
                onNext(240, 7),
                onCompleted(250)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->withLatestFrom([$e2]);
        });

        $this->assertMessages(
            [
                onNext(235, [4, 6]),
                onCompleted(240)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions([
            subscribe(200, 240)
        ], $e1->getSubscriptions());

        $this->assertSubscriptions([
            subscribe(200, 240)
        ], $e2->getSubscriptions());
    }

    /**
     * @test
     */
    public function withLatestFrom_consecutive_end_with_error_left()
    {
        $error = new \Exception();

        $e1 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(215, 2),
                onNext(225, 4),
                onError(230, $error)
            ]
        );

        $e2 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(235, 6),
                onNext(240, 7),
                onCompleted(250)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->withLatestFrom([$e2], [$this, 'add']);
        });

        $this->assertMessages(
            [
                onError(230, $error)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions([
            subscribe(200, 230)
        ], $e1->getSubscriptions());

        $this->assertSubscriptions([
            subscribe(200, 230)
        ], $e2->getSubscriptions());
    }

    /**
     * @test
     */
    public function withLatestFrom_consecutive_end_with_error_right()
    {
        $error = new \Exception();

        $e1 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(215, 2),
                onNext(225, 4),
                onCompleted(230)
            ]
        );

        $e2 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(235, 6),
                onNext(240, 7),
                onError(245, $error)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e2->withLatestFrom([$e1], [$this, 'add']);
        });

        $this->assertMessages(
            [
                onNext(235, 4 + 6),
                onNext(240, 4 + 7),
                onError(245, $error)
            ],
            $results->getMessages()
        );
    }

    /**
     * @test
     */
    public function withLatestFrom_selector_throws()
    {
        $error = new \Exception();

        $e1 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(220, 2),
                onCompleted(230)
            ]
        );

        $e2 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(215, 3),
                onCompleted(240)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->withLatestFrom([$e2], function () {
                throw new \Exception();
            });
        });

        $this->assertMessages(
            [
                onError(220, $error)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions([
            subscribe(200, 220)
        ], $e1->getSubscriptions());

        $this->assertSubscriptions([
            subscribe(200, 220)
        ], $e2->getSubscriptions());
    }

    /**
     * @test
     */
    public function withLatestFrom_return_return_dispose()
    {
        $e1 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(220, 2),
                onCompleted(230)
            ]
        );

        $e2 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(215, 3),
                onCompleted(240)
            ]
        );

        $results = $this->scheduler->startWithDispose(function () use ($e1, $e2) {
            return $e1->withLatestFrom([$e2], [$this, 'add']);
        }, 225);

        $this->assertMessages(
            [
                onNext(220, 2 + 3)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions([
            subscribe(200, 225)
        ], $e1->getSubscriptions());

        $this->assertSubscriptions([
            subscribe(200, 225)
        ], $e2->getSubscriptions());
    }

    public function testWithLatestFrom_multiple_dispose_no_selector()
    {
        $e1 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(220, 2),
                onCompleted(230)
            ]
        );

        $e2 = $this->createHotObservable(
            [
                onNext(150, 30),
                onNext(215, 40),
                onCompleted(240)
            ]
        );

        $e3 = $this->createHotObservable(
            [
                onNext(160, 500),
                onNext(217, 600),
                onCompleted(240)
            ]
        );

        $results = $this->scheduler->startWithDispose(function () use ($e1, $e2, $e3) {
            return $e1->withLatestFrom([$e2, $e3]);
        }, 225);

        $this->assertMessages(
            [
                onNext(220, [2, 40, 600])
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions([
            subscribe(200, 225)
        ], $e1->getSubscriptions());

        $this->assertSubscriptions([
            subscribe(200, 225)
        ], $e2->getSubscriptions());

        $this->assertSubscriptions([
            subscribe(200, 225)
        ], $e3->getSubscriptions());
    }

    public function testWithLatestFrom_multiple_dispose_with_selector()
    {
        $e1 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(220, 2),
                onCompleted(230)
            ]
        );

        $e2 = $this->createHotObservable(
            [
                onNext(150, 30),
                onNext(215, 40),
                onCompleted(240)
            ]
        );

        $e3 = $this->createHotObservable(
            [
                onNext(160, 500),
                onNext(217, 600),
                onCompleted(240)
            ]
        );

        $results = $this->scheduler->startWithDispose(function () use ($e1, $e2, $e3) {
            return $e1->withLatestFrom([$e2, $e3], function ($a, $b, $c) {
                return $a + $b + $c;
            });
        }, 225);

        $this->assertMessages(
            [
                onNext(220, 642)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions([
            subscribe(200, 225)
        ], $e1->getSubscriptions());

        $this->assertSubscriptions([
            subscribe(200, 225)
        ], $e2->getSubscriptions());

        $this->assertSubscriptions([
            subscribe(200, 225)
        ], $e3->getSubscriptions());
    }

    public function testWithLatestFrom_multiple_with_selector()
    {
        $e1 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(220, 2),
                onCompleted(230)
            ]
        );

        $e2 = $this->createHotObservable(
            [
                onNext(150, 30),
                onNext(215, 40),
                onCompleted(240)
            ]
        );

        $e3 = $this->createHotObservable(
            [
                onNext(160, 500),
                onNext(217, 600),
                onCompleted(240)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2, $e3) {
            return $e1->withLatestFrom([$e2, $e3], function ($a, $b, $c) {
                return $a + $b + $c;
            });
        });

        $this->assertMessages(
            [
                onNext(220, 642),
                onCompleted(230)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions([
            subscribe(200, 230)
        ], $e1->getSubscriptions());

        $this->assertSubscriptions([
            subscribe(200, 230)
        ], $e2->getSubscriptions());

        $this->assertSubscriptions([
            subscribe(200, 230)
        ], $e3->getSubscriptions());
    }
}
