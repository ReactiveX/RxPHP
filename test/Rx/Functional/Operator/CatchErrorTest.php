<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Rx\Disposable\CallbackDisposable;
use Rx\Functional\FunctionalTestCase;
use Rx\Observable;
use Rx\Observer\CallbackObserver;

class CatchErrorTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function catchError_NoErrors()
    {
        $o1 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(210, 2),
                onNext(220, 3),
                onCompleted(230)
            ]
        );

        $o2 = $this->createHotObservable(
            [
                onNext(150, 1),
                onCompleted(250)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($o1, $o2) {
            return $o1->catch(function () use ($o2) {
                return $o2;
            });
        });

        $this->assertMessages(
            [
                onNext(210, 2),
                onNext(220, 3),
                onCompleted(230)
            ],
            $results->getMessages()
        );
    }

    /**
     * @test
     */
    public function catchError_Never()
    {
        $o1 = Observable::never();

        $o2 = $this->createHotObservable(
            [
                onNext(150, 1),
                onCompleted(250)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($o1, $o2) {
            return $o1->catch(function () use ($o2) {
                return $o2;
            });
        });

        $this->assertMessages(
            [],
            $results->getMessages()
        );
    }

    /**
     * @test
     */
    public function catchError_Empty()
    {
        $o1 = $this->createHotObservable(
            [
                onNext(150, 1),
                onCompleted(230)
            ]
        );

        $o2 = $this->createHotObservable(
            [
                onNext(240, 5),
                onCompleted(250)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($o1, $o2) {
            return $o1->catch(function () use ($o2) {
                return $o2;
            });
        });

        $this->assertMessages(
            [
                onCompleted(230)
            ],
            $results->getMessages()
        );
    }

    /**
     * @test
     */
    public function catchError_Return()
    {
        $o1 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(210, 2),
                onCompleted(230)
            ]
        );

        $o2 = $this->createHotObservable(
            [
                onNext(240, 5),
                onCompleted(250)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($o1, $o2) {
            return $o1->catch(function () use ($o2) {
                return $o2;
            });
        });

        $this->assertMessages(
            [
                onNext(210, 2),
                onCompleted(230)
            ],
            $results->getMessages()
        );
    }

    /**
     * @test
     */
    public function catchError_Error()
    {
        $error = new \Exception();

        $o1 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(210, 2),
                onNext(220, 3),
                onError(230, $error)
            ]
        );

        $o2 = $this->createHotObservable(
            [
                onNext(240, 5),
                onCompleted(250)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($o1, $o2) {
            return $o1->catch(function () use ($o2) {
                return $o2;
            });
        });

        $this->assertMessages(
            [
                onNext(210, 2),
                onNext(220, 3),
                onNext(240, 5),
                onCompleted(250)
            ],
            $results->getMessages()
        );
    }

    /**
     * @test
     */
    public function catchError_Error_Never()
    {
        $error = new \Exception();

        $o1 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(210, 2),
                onNext(220, 3),
                onError(230, $error)
            ]
        );

        $o2 = Observable::never();

        $results = $this->scheduler->startWithCreate(function () use ($o1, $o2) {
            return $o1->catch(function () use ($o2) {
                return $o2;
            });
        });

        $this->assertMessages(
            [
                onNext(210, 2),
                onNext(220, 3)
            ],
            $results->getMessages()
        );
    }


    /**
     * @test
     */
    public function catchError_Error_Never_Dispose()
    {
        $error = new \Exception();

        $o1 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(210, 2),
                onNext(220, 3),
                onError(230, $error)
            ]
        );

        $o2 = Observable::never();

        $results = $this->scheduler->startWithDispose(function () use ($o1, $o2) {
            return $o1->catch(function () use ($o2) {
                return $o2;
            });
        }, 212);

        $this->assertMessages(
            [
                onNext(210, 2)
            ],
            $results->getMessages()
        );
    }

    /**
     * @test
     */
    public function catchError_Error_Error()
    {
        $error = new \Exception();

        $o1 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(210, 2),
                onNext(220, 3),
                onError(230, $error)
            ]
        );

        $o2 = $this->createHotObservable(
            [
                onNext(240, 4),
                onError(250, $error)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($o1, $o2) {
            return $o1->catch(function () use ($o2) {
                return $o2;
            });
        });

        $this->assertMessages(
            [
                onNext(210, 2),
                onNext(220, 3),
                onNext(240, 4),
                onError(250, $error)
            ],
            $results->getMessages()
        );
    }

    /**
     * @test
     */
    public function catchError_Error_Error_Dispose()
    {
        $error = new \Exception();

        $o1 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(210, 2),
                onNext(220, 3),
                onError(230, $error)
            ]
        );

        $o2 = $this->createHotObservable(
            [
                onNext(240, 4),
                onError(250, $error)
            ]
        );

        $results = $this->scheduler->startWithDispose(function () use ($o1, $o2) {
            return $o1->catch(function () use ($o2) {
                return $o2;
            });
        }, 225);

        $this->assertMessages(
            [
                onNext(210, 2),
                onNext(220, 3)
            ],
            $results->getMessages()
        );
    }


    /**
     * @test
     */
    public function catchError_HandlerThrows()
    {
        $error = new \Exception();

        $error2 = new \InvalidArgumentException();

        $o1 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(210, 2),
                onNext(220, 3),
                onError(230, $error)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($o1, $error2) {
            return $o1->catch(function () use ($error2) {
                throw $error2;

            });
        });

        $this->assertMessages(
            [
                onNext(210, 2),
                onNext(220, 3),
                onError(230, $error2)
            ],
            $results->getMessages()
        );
    }

    /**
     * @test
     */
    public function catchError_handler_returns_invalid_string()
    {
        $o1 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(210, 2),
                onNext(220, 3),
                onError(230, new \Exception())
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($o1) {
            return $o1->catch(function () {
                return 'unexpected string';
            });
        });

        $this->assertMessages(
            [
                onNext(210, 2),
                onNext(220, 3),
                onError(230, new \Exception())
            ],
            $results->getMessages()
        );
    }

    /**
     * @test
     */
    public function catchError_Nested_OuterCatches()
    {
        $error               = new \Exception();
        $firstHandlerCalled  = false;
        $secondHandlerCalled = false;

        $o1 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(210, 2),
                onError(215, $error)
            ]
        );

        $o2 = $this->createHotObservable(
            [
                onNext(220, 3),
                onCompleted(225)
            ]
        );

        $o3 = $this->createHotObservable(
            [
                onNext(220, 4),
                onCompleted(225)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($o1, $o2, $o3, &$firstHandlerCalled, &$secondHandlerCalled) {
            return $o1
                ->catch(function () use ($o2, &$firstHandlerCalled) {
                    $firstHandlerCalled = true;
                    return $o2;
                })
                ->catch(function () use ($o3, &$secondHandlerCalled) {
                    $secondHandlerCalled = true;
                    return $o3;
                });
        });

        $this->assertMessages(
            [
                onNext(210, 2),
                onNext(220, 3),
                onCompleted(225)
            ],
            $results->getMessages()
        );

        $this->assertTrue($firstHandlerCalled);
        $this->assertFalse($secondHandlerCalled);
    }

    /**
     * @test
     */
    public function catchError_ThrowFromNestedCatch()
    {
        $error  = new \Exception();
        $error2 = new \InvalidArgumentException();

        $firstHandlerCalled  = false;
        $secondHandlerCalled = false;

        $o1 = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(210, 2),
                onError(215, $error)
            ]
        );

        $o2 = $this->createHotObservable(
            [
                onNext(220, 3),
                onError(225, $error2)
            ]
        );

        $o3 = $this->createHotObservable(
            [
                onNext(230, 4),
                onCompleted(235)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($o1, $o2, $o3, &$firstHandlerCalled, &$secondHandlerCalled, $error, $error2) {
            return $o1
                ->catch(function ($e) use ($o2, &$firstHandlerCalled, $error) {
                    $firstHandlerCalled = true;
                    $this->assertSame($e, $error);

                    return $o2;
                })
                ->catch(function ($e) use ($o3, &$secondHandlerCalled, $error2) {
                    $secondHandlerCalled = true;
                    $this->assertSame($e, $error2);

                    return $o3;
                });
        });

        $this->assertMessages(
            [
                onNext(210, 2),
                onNext(220, 3),
                onNext(230, 4),
                onCompleted(235)
            ],
            $results->getMessages()
        );

        $this->assertTrue($firstHandlerCalled);
        $this->assertTrue($secondHandlerCalled);
    }

    /**
     * does not lose subscription to underlying observable
     * @test
     */
    public function catchError_does_not_lose_subscription()
    {
        $subscribes   = 0;
        $unsubscribes = 0;

        $tracer = Observable::create(function () use (&$subscribes, &$unsubscribes) {
            ++$subscribes;

            return new CallbackDisposable(function () use (&$unsubscribes) {
                ++$unsubscribes;
            });
        });

        // Try it without catchError()
        $s = $tracer->subscribe(new CallbackObserver());
        $this->assertEquals($subscribes, 1, '1 subscribes');
        $this->assertEquals($unsubscribes, 0, '0 unsubscribes');

        $s->dispose();
        $this->assertEquals($subscribes, 1, 'After dispose: 1 subscribes');
        $this->assertEquals($unsubscribes, 1, 'After dispose: 1 unsubscribes');

        // And now try again with catchError(function()):
        $subscribes   = 0;
        $unsubscribes = 0;

        $s = $tracer->catch(function () {
            return Observable::never();
        })->subscribe(new CallbackObserver());

        $this->assertEquals($subscribes, 1, 'catchError(Observable): 1 subscribes');
        $this->assertEquals($unsubscribes, 0, 'catchError(Observable): 0 unsubscribes');

        $s->dispose();
        $this->assertEquals($subscribes, 1, 'catchError(Observable): After dispose: 1 subscribes');
        $this->assertEquals($unsubscribes, 1, 'catchError(Observable): After dispose: 1 unsubscribes');
    }
}
