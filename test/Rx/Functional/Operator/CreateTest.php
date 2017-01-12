<?php

declare(strict_types = 1);

namespace Rx\Functional;

use Rx\Disposable\CallbackDisposable;
use Rx\Disposable\EmptyDisposable;
use Rx\Observable;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

class CreateTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function create_next()
    {

        $results = $this->scheduler->startWithCreate(function () {
            return Observable::create(function (ObserverInterface $o) {
                $o->onNext(1);
                $o->onNext(2);
                return new EmptyDisposable();
            });
        });

        $this->assertMessages([
            onNext(200, 1),
            onNext(200, 2)
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function create_null_disposable()
    {

        $results = $this->scheduler->startWithCreate(function () {
            return Observable::create(function (ObserverInterface $o) {
                $o->onNext(1);
                $o->onNext(2);
            });
        });

        $this->assertMessages([
            onNext(200, 1),
            onNext(200, 2)
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function create_completed()
    {

        $results = $this->scheduler->startWithCreate(function () {
            return Observable::create(function (ObserverInterface $o) {
                $o->onCompleted();
                $o->onNext(100);
                $o->onError(new \Exception());
                $o->onCompleted();
                return new EmptyDisposable();
            });
        });

        $this->assertMessages([
            onCompleted(200)
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function create_error()
    {

        $error = new \Exception();

        $results = $this->scheduler->startWithCreate(function () use ($error) {
            return Observable::create(function (ObserverInterface $o) use ($error) {
                $o->onError($error);
                $o->onNext(100);
                $o->onError(new \Exception());
                $o->onCompleted();
                return new EmptyDisposable();
            });
        });

        $this->assertMessages([
            onError(200, $error)
        ], $results->getMessages());
    }

    /**
     * @test
     * @expectedException \Exception
     *
     */
    public function create_throws_errors()
    {
        Observable::create(function ($o) {
            throw new \Exception;
        })->subscribe(new CallbackObserver());
    }

    /**
     * @test
     */
    public function create_dispose()
    {

        $results = $this->scheduler->startWithCreate(function () {
            return Observable::create(function (ObserverInterface $o) {

                $isStopped = false;

                $o->onNext(1);
                $o->onNext(2);

                $this->scheduler->scheduleAbsolute(600, function () use ($o, &$isStopped) {
                    if (!$isStopped) {
                        $o->onNext(3);
                    }
                });

                $this->scheduler->scheduleAbsolute(700, function () use ($o, &$isStopped) {
                    if (!$isStopped) {
                        $o->onNext(4);
                    }
                });

                $this->scheduler->scheduleAbsolute(900, function () use ($o, &$isStopped) {
                    if (!$isStopped) {
                        $o->onNext(5);
                    }
                });

                $this->scheduler->scheduleAbsolute(1100, function () use ($o, &$isStopped) {
                    if (!$isStopped) {
                        $o->onNext(6);
                    }
                });

                return new CallbackDisposable(function () use (&$isStopped) {
                    $isStopped = true;
                });
            });
        });

        $this->assertMessages([
            onNext(200, 1),
            onNext(200, 2),
            onNext(600, 3),
            onNext(700, 4),
            onNext(900, 5)
        ], $results->getMessages());
    }

    /**
     * @test
     *
     */
    public function create_observer_does_not_catch()
    {
        $this->assertException(function () {
            Observable::create(function (ObserverInterface $o) {
                $o->onNext(1);
                return new EmptyDisposable();
            })->subscribe(new CallbackObserver(function () {
                throw new \Exception;
            }));
        });


        $this->assertException(function () {
            Observable::create(function (ObserverInterface $o) {
                $o->onError(new \Exception());
                return new EmptyDisposable();
            })->subscribe(
                new CallbackObserver(
                    null,
                    function () {
                        throw new \Exception;
                    }
                )
            );
        });

        $this->assertException(function () {
            Observable::create(function (ObserverInterface $o) {
                $o->onCompleted();
                return new EmptyDisposable();
            })->subscribe(
                new CallbackObserver(
                    null,
                    null,
                    function () {
                        throw new \Exception;
                    }
                )
            );
        });
    }
}
