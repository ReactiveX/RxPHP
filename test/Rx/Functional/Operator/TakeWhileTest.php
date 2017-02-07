<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable;

class TakeWhileTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function takeWhile_never()
    {
        $xs = $this->createHotObservable([
            onNext(90, -1),
            onNext(110, -1),
            onNext(210, 2),
            onNext(260, 5),
            onNext(290, 13),
            onNext(320, 3),
            onCompleted(330),
            onNext(350, 7),
            onNext(390, 4),
            onNext(410, 17),
            onNext(450, 8),
            onNext(500, 23),
            onCompleted(600)
        ]);

        $invoked = 0;

        $results = $this->scheduler->startWithCreate(function () use ($xs, &$invoked) {
            return $xs->takeWhile(function ($x) use (&$invoked) {
                $invoked++;
                return $this->isPrime($x);
            });
        });

        $this->assertMessages([
            onNext(210, 2),
            onNext(260, 5),
            onNext(290, 13),
            onNext(320, 3),
            onCompleted(330)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 330)
        ], $xs->getSubscriptions());

        $this->assertEquals(4, $invoked);
    }

    /**
     * @test
     */
    public function takeWhile_complete_after()
    {
        $xs = $this->createHotObservable([
            onNext(90, -1),
            onNext(110, -1),
            onNext(210, 2),
            onNext(260, 5),
            onNext(290, 13),
            onNext(320, 3),
            onNext(350, 7),
            onNext(390, 4),
            onNext(410, 17),
            onNext(450, 8),
            onNext(500, 23),
            onCompleted(600)
        ]);

        $invoked = 0;

        $results = $this->scheduler->startWithCreate(function () use ($xs, &$invoked) {
            return $xs->takeWhile(function ($x) use (&$invoked) {
                $invoked++;
                return $this->isPrime($x);
            });
        });

        $this->assertMessages([
            onNext(210, 2),
            onNext(260, 5),
            onNext(290, 13),
            onNext(320, 3),
            onNext(350, 7),
            onCompleted(390)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 390)
        ], $xs->getSubscriptions());

        $this->assertEquals(6, $invoked);
    }

    /**
     * @test
     */
    public function takeWhile_error_before()
    {
        $error = new \Exception();

        $xs = $this->createHotObservable([
            onNext(90, -1),
            onNext(110, -1),
            onNext(210, 2),
            onNext(260, 5),
            onError(270, $error),
            onNext(290, 13),
            onNext(320, 3),
            onNext(350, 7),
            onNext(390, 4),
            onNext(410, 17),
            onNext(450, 8),
            onNext(500, 23)
        ]);

        $invoked = 0;

        $results = $this->scheduler->startWithCreate(function () use ($xs, &$invoked) {
            return $xs->takeWhile(function ($x) use (&$invoked) {
                $invoked++;
                return $this->isPrime($x);
            });
        });

        $this->assertMessages([
            onNext(210, 2),
            onNext(260, 5),
            onError(270, $error)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 270)
        ], $xs->getSubscriptions());

        $this->assertEquals(2, $invoked);
    }

    /**
     * @test
     */
    public function takeWhile_error_after()
    {
        $xs = $this->createHotObservable([
            onNext(90, -1),
            onNext(110, -1),
            onNext(210, 2),
            onNext(260, 5),
            onNext(290, 13),
            onNext(320, 3),
            onNext(350, 7),
            onNext(390, 4),
            onNext(410, 17),
            onNext(450, 8),
            onNext(500, 23),
            onError(600, new \Exception())
        ]);

        $invoked = 0;

        $results = $this->scheduler->startWithCreate(function () use ($xs, &$invoked) {
            return $xs->takeWhile(function ($x) use (&$invoked) {
                $invoked++;
                return $this->isPrime($x);
            });
        });

        $this->assertMessages([
            onNext(210, 2),
            onNext(260, 5),
            onNext(290, 13),
            onNext(320, 3),
            onNext(350, 7),
            onCompleted(390)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 390)
        ], $xs->getSubscriptions());

        $this->assertEquals(6, $invoked);
    }

    /**
     * @test
     */
    public function takeWhile_dispose_before()
    {
        $xs = $this->createHotObservable([
            onNext(90, -1),
            onNext(110, -1),
            onNext(210, 2),
            onNext(260, 5),
            onNext(290, 13),
            onNext(320, 3),
            onNext(350, 7),
            onNext(390, 4),
            onNext(410, 17),
            onNext(450, 8),
            onNext(500, 23),
            onCompleted(600)
        ]);

        $invoked = 0;

        $results = $this->scheduler->startWithDispose(function () use ($xs, &$invoked) {
            return $xs->takeWhile(function ($x) use (&$invoked) {
                $invoked++;
                return $this->isPrime($x);
            });
        }, 300);

        $this->assertMessages([
            onNext(210, 2),
            onNext(260, 5),
            onNext(290, 13)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 300)
        ], $xs->getSubscriptions());

        $this->assertEquals(3, $invoked);
    }

    /**
     * @test
     */
    public function takeWhile_dispose_after()
    {

        $xs = $this->createHotObservable([
            onNext(90, -1),
            onNext(110, -1),
            onNext(210, 2),
            onNext(260, 5),
            onNext(290, 13),
            onNext(320, 3),
            onNext(350, 7),
            onNext(390, 4),
            onNext(410, 17),
            onNext(450, 8),
            onNext(500, 23),
            onCompleted(600)
        ]);

        $invoked = 0;

        $results = $this->scheduler->startWithDispose(function () use ($xs, &$invoked) {
            return $xs->takeWhile(function ($x) use (&$invoked) {
                $invoked++;
                return $this->isPrime($x);
            });
        }, 400);

        $this->assertMessages([
            onNext(210, 2),
            onNext(260, 5),
            onNext(290, 13),
            onNext(320, 3),
            onNext(350, 7),
            onCompleted(390)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 390)
        ], $xs->getSubscriptions());

        $this->assertEquals(6, $invoked);
    }

    /**
     * @test
     */
    public function takeWhile_zero()
    {
        $xs = $this->createHotObservable([
            onNext(90, -1),
            onNext(110, -1),
            onNext(205, 100),
            onNext(210, 2),
            onNext(260, 5),
            onNext(290, 13),
            onNext(320, 3),
            onNext(350, 7),
            onNext(390, 4),
            onNext(410, 17),
            onNext(450, 8),
            onNext(500, 23),
            onCompleted(600)
        ]);

        $invoked = 0;

        $results = $this->scheduler->startWithDispose(function () use ($xs, &$invoked) {
            return $xs->takeWhile(function ($x) use (&$invoked) {
                $invoked++;
                return $this->isPrime($x);
            });
        }, 300);

        $this->assertMessages([
            onCompleted(205)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 205)
        ], $xs->getSubscriptions());

        $this->assertEquals(1, $invoked);
    }


    /**
     * @test
     */
    public function takeWhile_throw()
    {
        $error = new \Exception();

        $xs = $this->createHotObservable([
            onNext(90, -1),
            onNext(110, -1),
            onNext(210, 2),
            onNext(260, 5),
            onNext(290, 13),
            onNext(320, 3),
            onNext(350, 7),
            onNext(390, 4),
            onNext(410, 17),
            onNext(450, 8),
            onNext(500, 23),
            onCompleted(600)
        ]);

        $invoked = 0;

        $results = $this->scheduler->startWithCreate(function () use ($xs, &$invoked, $error) {
            return $xs->takeWhile(function ($x) use (&$invoked, $error) {
                $invoked++;
                if ($invoked === 3) {
                    throw $error;
                }
                return $this->isPrime($x);
            });
        });

        $this->assertMessages([
            onNext(210, 2),
            onNext(260, 5),
            onError(290, $error)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 290)
        ], $xs->getSubscriptions());

        $this->assertEquals(3, $invoked);
    }

    /**
     * @test
     */
    public function takeWhile_index()
    {
        $xs = $this->createHotObservable([
            onNext(90, -1),
            onNext(110, -1),
            onNext(205, 100),
            onNext(210, 2),
            onNext(260, 5),
            onNext(290, 13),
            onNext(320, 3),
            onNext(350, 7),
            onNext(390, 4),
            onNext(410, 17),
            onNext(450, 8),
            onNext(500, 23),
            onCompleted(600)
        ]);


        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->takeWhileWithIndex(function ($i) {
                return $i < 5;
            });
        });

        $this->assertMessages([
            onNext(205, 100),
            onNext(210, 2),
            onNext(260, 5),
            onNext(290, 13),
            onNext(320, 3),
            onCompleted(350)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 350)
        ], $xs->getSubscriptions());
    }

    private function isPrime($num)
    {
        //1 is not prime. See: http://en.wikipedia.org/wiki/Prime_number#Primality_of_one
        if ($num === 1) {
            return false;
        }

        //2 is prime (the only even number that is prime)
        if ($num === 2) {
            return true;
        }

        /**
         * if the number is divisible by two, then it's not prime and it's no longer
         * needed to check other even numbers
         */
        if ($num % 2 == 0) {
            return false;
        }

        /**
         * Checks the odd numbers. If any of them is a factor, then it returns false.
         * The sqrt can be an aproximation, hence just for the sake of
         * security, one rounds it to the next highest integer value.
         */
        for ($i = 3; $i <= ceil(sqrt($num)); $i = $i + 2) {
            if ($num % $i === 0) {
                return false;
            }
        }

        return true;
    }
}
