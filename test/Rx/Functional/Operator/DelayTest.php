<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable;

class DelayTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function delay_relative_time_simple_1()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(250, 2),
            onNext(350, 3),
            onNext(450, 4),
            onCompleted(550)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->delay(100, $this->scheduler);
        });

        $this->assertMessages(
            [
                onNext(350, 2),
                onNext(450, 3),
                onNext(550, 4),
                onCompleted(650)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 550)
            ],
            $xs->getSubscriptions()
        );
    }

    /**
     * @test
     */
    public function delay_relative_time_simple_2_implementation()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(250, 2),
            onNext(350, 3),
            onNext(450, 4),
            onCompleted(550)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->delay(50, $this->scheduler);
        });

        $this->assertMessages(
            [
                onNext(300, 2),
                onNext(400, 3),
                onNext(500, 4),
                onCompleted(600)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 550)
            ],
            $xs->getSubscriptions()
        );
    }

    /**
     * @test
     */
    public function delay_relative_time_simple_3_implementation()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(250, 2),
            onNext(350, 3),
            onNext(450, 4),
            onCompleted(550)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->delay(150, $this->scheduler);
        });

        $this->assertMessages(
            [
                onNext(400, 2),
                onNext(500, 3),
                onNext(600, 4),
                onCompleted(700)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 550)
            ],
            $xs->getSubscriptions()
        );
    }

    /**
     * @test
     */
    public function delay_relative_time_error_1_implementation()
    {
        $error = new \Exception();

        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(250, 2),
            onNext(350, 3),
            onNext(450, 4),
            onError(550, $error)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->delay(50, $this->scheduler);
        });

        $this->assertMessages(
            [
                onNext(300, 2),
                onNext(400, 3),
                onNext(500, 4),
                onError(550, $error)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 550)
            ],
            $xs->getSubscriptions()
        );
    }

    /**
     * @test
     */
    public function delay_relative_time_error_2_implementation()
    {
        $error = new \Exception();

        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(250, 2),
            onNext(350, 3),
            onNext(450, 4),
            onError(550, $error)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->delay(150, $this->scheduler);
        });

        $this->assertMessages(
            [
                onNext(400, 2),
                onNext(500, 3),
                onError(550, $error)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 550)
            ],
            $xs->getSubscriptions()
        );
    }

    /**
     * @test
     */
    public function delay_empty()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(550)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->delay(10, $this->scheduler);
        });

        $this->assertMessages(
            [
                onCompleted(560)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 550)
            ],
            $xs->getSubscriptions()
        );
    }

    /**
     * @test
     */
    public function delay_error()
    {
        $error = new \Exception();

        $xs = $this->createHotObservable([
            onNext(150, 1),
            onError(550, $error)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->delay(10, $this->scheduler);
        });

        $this->assertMessages(
            [
                onError(550, $error)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 550)
            ],
            $xs->getSubscriptions()
        );
    }

    /**
     * @test
     */
    public function delay_never()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->delay(10, $this->scheduler);
        });

        $this->assertMessages(
            [
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

    /**
     * @test
     */
    public function delay_completes_during_subscribe_without_throwing()
    {
        $completes = false;

        Observable::create(function ($observer) {
            $observer->onCompleted();
        })->delay(1, $this->scheduler)->subscribe(
            null,
            null,
            function () use (&$completes) {
                $completes = true;
            }
        );

        $this->scheduler->start();

        $this->assertTrue($completes);
    }
    
    /**
     * @test
     */
    public function delay_disposed_after_emit()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(250, 2),
            onNext(299, 3),
            onNext(350, 4),
            onCompleted(351)
        ]);
        
        $results = $this->scheduler->startWithDispose(function () use ($xs) {
            return $xs->delay(5, $this->scheduler);
        }, 300);
        
        $this->assertMessages([
            onNext(255, 2)
        ], $results->getMessages());
        
        $this->assertSubscriptions([
            subscribe(200, 300)
        ], $xs->getSubscriptions());
    }
}
