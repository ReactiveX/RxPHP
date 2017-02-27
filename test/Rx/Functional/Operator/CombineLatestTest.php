<?php

declare(strict_types = 1);


namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable;
use Rx\Observable\NeverObservable;

class CombineLatestTest extends FunctionalTestCase
{
    public function add($a, $b)
    {
        return $a + $b;
    }

    /**
     * @test
     */
    public function combineLatest_never_never()
    {

        $e1 = new NeverObservable();
        $e2 = new NeverObservable();


        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->combineLatest([$e2], [$this, 'add']);
        });

        $this->assertMessages([], $results->getMessages());
    }

    /**
     * @test
     */
    public function combineLatest_never_empty()
    {

        $e1 = new NeverObservable();
        $e2 = $this->createHotObservable(
            [
                onNext(150, 1),
                onCompleted(210)
            ]
        );


        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->combineLatest([$e2], [$this, 'add']);
        });

        $this->assertMessages([], $results->getMessages());
    }

    /**
     * @test
     */
    public function combineLatest_empty_never()
    {
        $e1 = new NeverObservable();
        $e2 = $this->createHotObservable(
            [
                onNext(150, 1),
                onCompleted(210)
            ]
        );


        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e2->combineLatest([$e1], [$this, 'add']);
        });

        $this->assertMessages([], $results->getMessages());
    }


    /**
     * @test
     */
    public function combineLatest_empty_empty()
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
            return $e2->combineLatest([$e1], [$this, 'add']);
        });

        $this->assertMessages([onCompleted(210)], $results->getMessages());
    }


    /**
     * @test
     */
    public function combineLatest_empty_return()
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
            return $e1->combineLatest([$e2], [$this, 'add']);
        });

        $this->assertMessages([onCompleted(215)], $results->getMessages());
    }

    /**
     * @test
     */
    public function combineLatest_never_return()
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
            return $e1->combineLatest([$e2], [$this, 'add']);
        });

        $this->assertMessages([], $results->getMessages());
    }

    /**
     * @test
     */
    public function combineLatest_return_return()
    {
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
                onNext(220, 3),
                onCompleted(240)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->combineLatest([$e2], [$this, 'add']);
        });

        $this->assertMessages(
            [
                onNext(220, 2 + 3),
                onCompleted(240)
            ],
            $results->getMessages()
        );
    }


    /**
     * @test
     */
    public function combineLatest_return_return_return()
    {
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
                onNext(220, 3),
                onCompleted(240)
            ]
        );

        $e3 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(245, 4),
                onCompleted(250)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2, $e3) {
            return $e1->combineLatest([$e2, $e3]);
        });

        $this->assertMessages(
            [
                onNext(245, [2, 3, 4]),
                onCompleted(250)
            ],
            $results->getMessages()
        );
    }

    /**
     * @test
     */
    public function combineLatest_return_return_no_selector()
    {
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
                onNext(220, 3),
                onCompleted(240)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->combineLatest([$e2]);
        });

        $this->assertMessages(
            [
                onNext(220, [2, 3]),
                onCompleted(240)
            ],
            $results->getMessages()
        );
    }


    /**
     * @test
     */
    public function combineLatest_empty_error()
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
            return $e1->combineLatest([$e2], [$this, 'add']);
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
    public function combineLatest_error_empty()
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
            return $e2->combineLatest([$e1], [$this, 'add']);
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
    public function combineLatest_return_throw()
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
            return $e1->combineLatest([$e2], [$this, 'add']);
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
    public function combineLatest_throw_return()
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
            return $e2->combineLatest([$e1], [$this, 'add']);
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
    public function combineLatest_throw_throw()
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
            return $e1->combineLatest([$e2], [$this, 'add']);
        });

        $this->assertMessages(
            [
                onError(220, $error1)
            ],
            $results->getMessages()
        );
    }

    /**
     * @test
     */
    public function combineLatest_error_throw()
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
            return $e1->combineLatest([$e2], [$this, 'add']);
        });

        $this->assertMessages(
            [
                onError(220, $error1)
            ],
            $results->getMessages()
        );
    }

    /**
     * @test
     */
    public function combineLatest_throw_error()
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
            return $e2->combineLatest([$e1], [$this, 'add']);
        });

        $this->assertMessages(
            [
                onError(220, $error1)
            ],
            $results->getMessages()
        );
    }


    /**
     * @test
     */
    public function combineLatest_never_throw()
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
            return $e1->combineLatest([$e2], [$this, 'add']);
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
    public function combineLatest_throw_never()
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
            return $e2->combineLatest([$e1], [$this, 'add']);
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
    public function combineLatest_some_throw()
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
            return $e1->combineLatest([$e2], [$this, 'add']);
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
    public function combineLatest_throw_some()
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
            return $e2->combineLatest([$e1], [$this, 'add']);
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
    public function combineLatest_throw_after_complete_left()
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
            return $e1->combineLatest([$e2], [$this, 'add']);
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
    public function combineLatest_throw_after_complete_right()
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
            return $e2->combineLatest([$e1], [$this, 'add']);
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
    public function combineLatest_interleaved_with_tail()
    {

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
                onNext(220, 3),
                onNext(230, 5),
                onNext(235, 6),
                onNext(240, 7),
                onCompleted(250)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->combineLatest([$e2], [$this, 'add']);
        });

        $this->assertMessages(
            [
                onNext(220, 2 + 3),
                onNext(225, 3 + 4),
                onNext(230, 4 + 5),
                onNext(235, 4 + 6),
                onNext(240, 4 + 7),
                onCompleted(250)
            ],
            $results->getMessages()
        );
    }


    /**
     * @test
     */
    public function combineLatest_consecutive()
    {
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
                onCompleted(250)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->combineLatest([$e2], [$this, 'add']);
        });

        $this->assertMessages(
            [
                onNext(235, 4 + 6),
                onNext(240, 4 + 7),
                onCompleted(250)
            ],
            $results->getMessages()
        );
    }

    /**
     * @test
     */
    public function combineLatest_consecutive_end_with_error_left()
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
            return $e1->combineLatest([$e2], [$this, 'add']);
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
    public function combineLatest_consecutive_end_with_error_right()
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
            return $e1->combineLatest([$e2], [$this, 'add']);
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
    public function combineLatest_selector_throws()
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
                onNext(220, 3),
                onCompleted(240)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->combineLatest([$e2], function () {
                throw new \Exception();
            });
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
    public function combineLatest_delay()
    {
        $source1 = Observable::timer(100, $this->scheduler);
        $source2 = Observable::timer(120, $this->scheduler);
        $source3 = Observable::timer(140, $this->scheduler);

        $source = $source1->combineLatest([$source2, $source3]);

        $result = $this->scheduler->startWithCreate(function () use ($source) {
            return $source;
        });

        $this->assertMessages([
            onNext(340, [0, 0, 0]),
            onCompleted(340)
        ], $result->getMessages());
    }

    /**
     * @test
     */
    public function combineLatest_args_order()
    {

        $e1 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(600, 2),
                onCompleted(650)
            ]
        );

        $e2 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(220, 1),
                onCompleted(250)
            ]
        );

        $e3 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(700, 3),
                onCompleted(750)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2, $e3) {
            return $e1->combineLatest([$e2, $e3]);
        });

        $this->assertMessages(
            [
                onNext(700, [2, 1, 3]),
                onCompleted(750)
            ],
            $results->getMessages()
        );
    }
}