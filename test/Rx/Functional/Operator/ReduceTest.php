<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;


use Rx\Functional\FunctionalTestCase;
use Rx\Notification\OnErrorNotification;
use Rx\Testing\MockObserver;
use Rx\Testing\Recorded;


class ReduceTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function reduce_with_seed_empty()
    {
        $xs = $this->createHotObservable([
          onNext(150, 1),
          onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->reduce(function ($acc, $x) {
                return $acc + $x;
            }, 42);
        });

        $this->assertMessages([onNext(250, 42), onCompleted(250)], $results->getMessages());
    }


    /**
     * @test
     */
    public function reduce_with_seed_return()
    {
        $xs = $this->createHotObservable([
          onNext(150, 1),
          onNext(210, 24),
          onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->reduce(function ($acc, $x) {
                return $acc + $x;
            }, 42);
        });

        $this->assertMessages([onNext(250, 42 + 24), onCompleted(250)], $results->getMessages());
    }

    /**
     * @test
     */
    public function reduce_with_seed_throw()
    {
        $ex = new \Exception('ex');
        $xs = $this->createHotObservable([
          onNext(150, 1),
          onError(210, $ex)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->reduce(function ($acc, $x) {
                return $acc + $x;
            }, 42);
        });

        $this->assertMessages([onError(210, $ex)], $results->getMessages());
    }

    /**
     * @test
     */
    public function reduce_with_seed_never()
    {
        $xs = $this->createHotObservable([
          onNext(150, 1)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->reduce(function ($acc, $x) {
                return $acc + $x;
            }, 42);
        });

        $this->assertMessages([], $results->getMessages());
    }

    /**
     * @test
     */
    public function reduce_with_seed_range()
    {
        $xs = $this->createHotObservable([
          onNext(150, 1),
          onNext(210, 0),
          onNext(220, 1),
          onNext(230, 2),
          onNext(240, 3),
          onNext(250, 4),
          onCompleted(260)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->reduce(function ($acc, $x) {
                return $acc + $x;
            }, 42);
        });

        $this->assertMessages([onNext(260, 10 + 42), onCompleted(260)], $results->getMessages());
    }

    /**
     * @test
     */
    public function reduce_without_seed_empty()
    {
        $xs = $this->createHotObservable([
          onNext(150, 1),
          onCompleted(250)
        ]);

        /** @var $results MockObserver * */
        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->reduce(function ($acc, $x) {
                return $acc + $x;
            });
        });

        $messages = $results->getMessages();
        $this->assertEquals(1, count($messages));
        $this->assertTrue($messages[0] instanceof Recorded && $messages[0]->getValue() instanceof OnErrorNotification);
        $this->assertEquals(250, $messages[0]->getTime());

    }

    /**
     * @test
     */
    public function reduce_without_seed_return()
    {
        $xs = $this->createHotObservable([
          onNext(150, 1),
          onNext(210, 24),
          onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->reduce(function ($acc, $x) {
                return $acc + $x;
            });
        });

        $this->assertMessages([onNext(250, 24), onCompleted(250)], $results->getMessages());
    }

    /**
     * @test
     */
    public function reduce_without_seed_throw()
    {
        $ex = new \Exception('ex');
        $xs = $this->createHotObservable([
          onNext(150, 1),
          onError(210, $ex)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->reduce(function ($acc, $x) {
                return $acc + $x;
            });
        });

        $this->assertMessages([onError(210, $ex)], $results->getMessages());
    }

    /**
     * @test
     */
    public function reduce_without_seed_never()
    {
        $xs = $this->createHotObservable([
          onNext(150, 1)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->reduce(function ($acc, $x) {
                return $acc + $x;
            });
        });

        $this->assertMessages([], $results->getMessages());
    }

    /**
     * @test
     */
    public function reduce_without_seed_range()
    {
        $xs = $this->createHotObservable([
          onNext(150, 1),
          onNext(210, 0),
          onNext(220, 1),
          onNext(230, 2),
          onNext(240, 3),
          onNext(250, 4),
          onCompleted(260)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->reduce(function ($acc, $x) {
                return $acc + $x;
            });
        });

        $this->assertMessages([onNext(260, 10), onCompleted(260)], $results->getMessages());
    }

    /**
     * @test
     */
    public function reduce_accumulator_throws()
    {
        $xs = $this->createHotObservable(
          [
            onNext(150, 1),
            onNext(210, 2),
            onNext(230, 3),
            onCompleted(240)
          ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->reduce(function () {
                throw new \Exception();
            });
        });

        $this->assertMessages([onError(230, new \Exception())], $results->getMessages());
    }

    /**
     * @test
     */
    public function reduce_accumulator_throws_with_seed()
    {
        $xs = $this->createHotObservable(
          [
            onNext(150, 1),
            onNext(210, 2),
            onNext(230, 3),
            onCompleted(240)
          ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->reduce(function () {
                throw new \Exception();
            }, 42);
        });

        $this->assertMessages([onError(210, new \Exception())], $results->getMessages());
    }

    /**
     * @test
     */
    public function reduce_with_falsy_seed_range()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(220, 1),
            onNext(230, 2),
            onNext(240, 3),
            onNext(250, 4),
            onCompleted(260)
        ]);

        $accums = [];

        $results = $this->scheduler->startWithCreate(function () use ($xs, &$accums) {
            return $xs->reduce(function ($acc, $x) use (&$accums){
                $accums[] = $acc;
                return $x;
            }, 0);
        });

        $this->assertEquals($accums, [0, 1, 2, 3]);
        $this->assertMessages([onNext(260, 4), onCompleted(260)], $results->getMessages());
    }
}
