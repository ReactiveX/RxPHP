<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Observer\CallbackObserver;

class DoOnEachOperatorTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function doOnEach_should_see_all_values(): void
    {

        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onNext(220, 3),
            onNext(230, 4),
            onNext(240, 5),
            onCompleted(250)
        ]);

        $i   = 0;
        $sum = 2 + 3 + 4 + 5;

        $this->scheduler->startWithCreate(function () use ($xs, &$i, &$sum) {
            return $xs->doOnEach(new CallbackObserver(function ($x) use (&$i, &$sum) {
                $i++;

                return $sum -= $x;
            }));
        });

        $this->assertEquals(4, $i);
        $this->assertEquals(0, $sum);
    }

    /**
     * @test
     */
    public function doOnEach_plain_action(): void
    {

        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onNext(220, 3),
            onNext(230, 4),
            onNext(240, 5),
            onCompleted(250)
        ]);

        $i = 0;

        $this->scheduler->startWithCreate(function () use ($xs, &$i) {
            return $xs->doOnEach(new CallbackObserver(function ($x) use (&$i) {
                return $i++;
            }));
        });

        $this->assertEquals(4, $i);
    }

    /**
     * @test
     */
    public function doOnEach_next_completed(): void
    {

        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onNext(220, 3),
            onNext(230, 4),
            onNext(240, 5),
            onCompleted(250)
        ]);

        $i         = 0;
        $sum       = 2 + 3 + 4 + 5;
        $completed = false;

        $this->scheduler->startWithCreate(function () use ($xs, &$i, &$sum, &$completed) {
            return $xs->doOnEach(new CallbackObserver(
                function ($x) use (&$i, &$sum) {
                    $i++;

                    return $sum -= $x;
                },
                null,
                function () use (&$completed): void {
                    $completed = true;
                }
            ));
        });


        $this->assertEquals(4, $i);
        $this->assertEquals(0, $sum);
        $this->assertTrue($completed);
    }

    /**
     * @test
     */
    public function doOnEach_next_completed_never(): void
    {
        $xs = $this->createHotObservable([
            onNext(150, 1)
        ]);

        $i         = 0;
        $completed = false;

        $this->scheduler->startWithCreate(function () use ($xs, &$i, &$completed) {
            return $xs->do(new CallbackObserver(
                function ($x) use (&$i): void {
                    $i++;

                },
                null,
                function () use (&$completed): void {
                    $completed = true;
                }
            ));
        });


        $this->assertEquals(0, $i);
        $this->assertFalse($completed);
    }

    /**
     * @test
     */
    public function doOnEach_next_error(): void
    {

        $ex = new \Exception();

        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onNext(220, 3),
            onNext(230, 4),
            onNext(240, 5),
            onError(250, $ex)
        ]);

        $i        = 0;
        $sum      = 2 + 3 + 4 + 5;
        $sawError = false;

        $this->scheduler->startWithCreate(function () use ($xs, &$i, &$sum, &$sawError, $ex) {
            return $xs->do(new CallbackObserver(
                function ($x) use (&$i, &$sum) {
                    $i++;

                    return $sum -= $x;
                },

                function ($e) use (&$sawError, $ex): void {
                    $sawError = $e === $ex;
                }
            ));
        });


        $this->assertEquals(4, $i);
        $this->assertEquals(0, $sum);
        $this->assertTrue($sawError);
    }

    /**
     * @test
     */
    public function doOnEach_next_error_not(): void
    {

        $ex = new \Exception();

        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onNext(220, 3),
            onNext(230, 4),
            onNext(240, 5),
            onCompleted(250)
        ]);

        $i        = 0;
        $sum      = 2 + 3 + 4 + 5;
        $sawError = false;

        $this->scheduler->startWithCreate(function () use ($xs, &$i, &$sum, &$sawError, $ex) {
            return $xs->do(new CallbackObserver(
                function ($x) use (&$i, &$sum) {
                    $i++;

                    return $sum -= $x;
                },

                function ($e) use (&$sawError, $ex): void {
                    $sawError = $e === $ex;
                }
            ));
        });


        $this->assertEquals(4, $i);
        $this->assertEquals(0, $sum);
        $this->assertFalse($sawError);
    }

    /**
     * @test
     */
    public function doOnEach_next_error_completed(): void
    {

        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onNext(220, 3),
            onNext(230, 4),
            onNext(240, 5),
            onCompleted(250)
        ]);

        $i         = 0;
        $sum       = 2 + 3 + 4 + 5;
        $sawError  = false;
        $completed = false;

        $this->scheduler->startWithCreate(function () use ($xs, &$i, &$sum, &$completed, &$sawError) {
            return $xs->do(new CallbackObserver(
                function ($x) use (&$i, &$sum): void {
                    $i++;
                    $sum -= $x;
                },
                function () use (&$sawError): void {
                    $sawError = true;
                },
                function () use (&$completed): void {
                    $completed = true;
                }
            ));
        });


        $this->assertEquals(4, $i);
        $this->assertEquals(0, $sum);
        $this->assertFalse($sawError);
        $this->assertTrue($completed);
    }

    /**
     * @test
     */
    public function doOnEach_next_error_completed_error(): void
    {
        $ex = new \Exception();

        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onNext(220, 3),
            onNext(230, 4),
            onNext(240, 5),
            onError(250, $ex)
        ]);

        $i         = 0;
        $sum       = 2 + 3 + 4 + 5;
        $sawError  = false;
        $completed = false;

        $this->scheduler->startWithCreate(function () use ($xs, &$i, &$sum, &$completed, &$sawError) {
            return $xs->do(new CallbackObserver(
                function ($x) use (&$i, &$sum): void {
                    $i++;
                    $sum -= $x;
                },
                function () use (&$sawError): void {
                    $sawError = true;
                },
                function () use (&$completed): void {
                    $completed = true;
                }
            ));
        });


        $this->assertEquals(4, $i);
        $this->assertEquals(0, $sum);
        $this->assertTrue($sawError);
        $this->assertFalse($completed);
    }


    /**
     * @test
     */
    public function doOnEach_next_error_completed_never(): void
    {

        $xs = $this->createHotObservable([
            onNext(150, 1)
        ]);

        $i         = 0;
        $sawError  = false;
        $completed = false;

        $this->scheduler->startWithCreate(function () use ($xs, &$i, &$completed, &$sawError) {
            return $xs->do(new CallbackObserver(
                function ($x) use (&$i, &$sum): void {
                    $i++;
                },
                function () use (&$sawError): void {
                    $sawError = true;
                },
                function () use (&$completed): void {
                    $completed = true;
                }
            ));
        });


        $this->assertEquals(0, $i);
        $this->assertFalse($sawError);
        $this->assertFalse($completed);
    }

    /**
     * @test
     */
    public function doOnEach_next_next_throws(): void
    {
        $ex = new \Exception();

        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs, $ex) {
            return $xs->do(new CallbackObserver(function () use ($ex): void {
                throw $ex;
            }));
        });

        $this->assertMessages([onError(210, $ex)], $results->getMessages());

    }

    /**
     * @test
     */
    public function doOnEach_next_completed_next_throws(): void
    {
        $ex = new \Exception();

        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs, $ex) {
            return $xs->do(new CallbackObserver(
                function () use ($ex): void {
                    throw $ex;
                },
                null,
                function (): void {
                }));
        });

        $this->assertMessages([onError(210, $ex)], $results->getMessages());

    }

    /**
     * @test
     */
    public function doOnEach_next_completed_completed_throws(): void
    {
        $ex = new \Exception();

        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs, $ex) {
            return $xs->do(new CallbackObserver(
                function (): void {
                },
                null,
                function () use ($ex): void {
                    throw $ex;
                }));
        });

        $this->assertMessages([onNext(210, 2), onError(250, $ex)], $results->getMessages());

    }

    /**
     * @test
     */
    public function doOnEach_next_error_next_throws(): void
    {
        $ex = new \Exception();

        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs, $ex) {
            return $xs->do(new CallbackObserver(
                function () use ($ex): void {
                    throw $ex;
                },
                function (): void {
                }
            ));
        });

        $this->assertMessages([onError(210, $ex)], $results->getMessages());

    }

    /**
     * @test
     */
    public function doOnEach_next_error_error_throws(): void
    {
        $ex1 = new \Exception("error1");
        $ex2 = new \Exception("error2");

        $xs = $this->createHotObservable([
            onNext(150, 1),
            onError(210, $ex1)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs, $ex2) {
            return $xs->do(new CallbackObserver(
                function (): void {
                },
                function () use ($ex2): void {
                    throw $ex2;
                }
            ));
        });

        $this->assertMessages([onError(210, $ex2)], $results->getMessages());

    }


    /**
     * @test
     */
    public function doOnEach_next_error_completed_next_throws(): void
    {
        $ex = new \Exception();

        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs, $ex) {
            return $xs->do(new CallbackObserver(
                function () use ($ex): void {
                    throw $ex;
                },
                function (): void {
                },
                function (): void {
                }
            ));
        });

        $this->assertMessages([onError(210, $ex)], $results->getMessages());

    }

    /**
     * @test
     */
    public function doOnEach_next_error_completed_error_throws(): void
    {
        $ex1 = new \Exception("error1");
        $ex2 = new \Exception("error2");

        $xs = $this->createHotObservable([
            onNext(150, 1),
            onError(210, $ex1)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs, $ex2) {
            return $xs->do(new CallbackObserver(
                function (): void {
                },
                function () use ($ex2): void {
                    throw $ex2;
                },
                function (): void {
                }
            ));
        });

        $this->assertMessages([onError(210, $ex2)], $results->getMessages());

    }

    /**
     * @test
     */
    public function doOnEach_next_error_completed_completed_throws(): void
    {
        $ex = new \Exception();

        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs, $ex) {
            return $xs->do(new CallbackObserver(

                function (): void {
                },
                function (): void {
                },
                function () use ($ex): void {
                    throw $ex;
                }
            ));
        });

        $this->assertMessages([onNext(210, 2), onError(250, $ex)], $results->getMessages());

    }

    /**
     * @test
     */
    public function doOnEach_observer_next_throws(): void
    {
        $ex = new \Exception();

        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs, $ex) {
            return $xs->do(new CallbackObserver(

                function () use ($ex): void {
                    throw $ex;
                },
                function (): void {
                },
                function (): void {
                }
            ));
        });

        $this->assertMessages([onError(210, $ex)], $results->getMessages());

    }

    /**
     * @test
     */
    public function doOnEach_observer_error_throws(): void
    {
        $ex1 = new \Exception("error1");
        $ex2 = new \Exception("error2");

        $xs = $this->createHotObservable([
            onNext(150, 1),
            onError(210, $ex1)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs, $ex2) {
            return $xs->do(new CallbackObserver(
                function (): void {
                },
                function () use ($ex2): void {
                    throw $ex2;
                },
                function (): void {
                }
            ));
        });

        $this->assertMessages([onError(210, $ex2)], $results->getMessages());

    }

    /**
     * @test
     */
    public function doOnEach_observer_completed_throws(): void
    {
        $ex = new \Exception();

        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs, $ex) {
            return $xs->do(new CallbackObserver(
                function (): void { //noop
                },
                function (): void { //noop
                },
                function () use ($ex): void {
                    throw $ex;
                }
            ));
        });

        $this->assertMessages([onNext(210, 2), onError(250, $ex)], $results->getMessages());
    }


    /**
     * @test
     */
    public function do_plain_action(): void
    {

        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onNext(220, 3),
            onNext(230, 4),
            onNext(240, 5),
            onCompleted(250)
        ]);

        $i = 0;

        $this->scheduler->startWithCreate(function () use ($xs, &$i) {
            return $xs->do(function ($x) use (&$i) {
                return $i++;
            });
        });

        $this->assertEquals(4, $i);
    }

    /**
     * @test
     */
    public function do_next_completed(): void
    {

        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onNext(220, 3),
            onNext(230, 4),
            onNext(240, 5),
            onCompleted(250)
        ]);

        $i         = 0;
        $sum       = 2 + 3 + 4 + 5;
        $completed = false;

        $this->scheduler->startWithCreate(function () use ($xs, &$i, &$sum, &$completed) {
            return $xs->do(
                function ($x) use (&$i, &$sum) {
                    $i++;

                    return $sum -= $x;
                },
                null,
                function () use (&$completed): void {
                    $completed = true;
                }
            );
        });


        $this->assertEquals(4, $i);
        $this->assertEquals(0, $sum);
        $this->assertTrue($completed);
    }

    /**
     * @test
     */
    public function do_next_error(): void
    {

        $ex = new \Exception();

        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onNext(220, 3),
            onNext(230, 4),
            onNext(240, 5),
            onError(250, $ex)
        ]);

        $i        = 0;
        $sum      = 2 + 3 + 4 + 5;
        $sawError = false;

        $this->scheduler->startWithCreate(function () use ($xs, &$i, &$sum, &$sawError, $ex) {
            return $xs->do(
                function ($x) use (&$i, &$sum) {
                    $i++;

                    return $sum -= $x;
                },

                function ($e) use (&$sawError, $ex): void {
                    $sawError = $e === $ex;
                }
            );
        });

        $this->assertTrue($sawError);

    }

    /**
     * @test
     *
     */
    public function do_throws_when_args_invalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onNext(220, 3),
            onNext(230, 4),
            onNext(240, 5),
            onCompleted(250)
        ]);

        $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->do('invalid arg');
        });
    }
}