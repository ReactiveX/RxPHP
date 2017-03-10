<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable;

class TakeUntilTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function takeUntil_preempt_some_data_next()
    {
        $l = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onNext(220, 3),
            onNext(230, 4),
            onNext(240, 5),
            onCompleted(250)
        ]);

        $r = $this->createHotObservable([
            onNext(150, 1),
            onNext(225, 99),
            onCompleted(230)
        ]);


        $result = $this->scheduler->startWithCreate(function () use ($l, $r) {
            return $l->takeUntil($r);
        });

        $this->assertMessages(
            [
                onNext(210, 2),
                onNext(220, 3),
                onCompleted(225)
            ],
            $result->getMessages()
        );
    }

    /**
     * @test
     */
    public function takeUntil_preempt_some_data_error()
    {
        $error = new \Exception();

        $l = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onNext(220, 3),
            onNext(230, 4),
            onNext(240, 5),
            onCompleted(250)
        ]);

        $r = $this->createHotObservable([
            onNext(150, 1),
            onError(225, $error)
        ]);


        $result = $this->scheduler->startWithCreate(function () use ($l, $r) {
            return $l->takeUntil($r);
        });

        $this->assertMessages(
            [
                onNext(210, 2),
                onNext(220, 3),
                onError(225, $error)
            ],
            $result->getMessages()
        );
    }

    /**
     * @test
     */
    public function takeUntil_preempt_some_data_empty()
    {
        $l = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onNext(220, 3),
            onNext(230, 4),
            onNext(240, 5),
            onCompleted(250)
        ]);

        $r = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(225)
        ]);


        $result = $this->scheduler->startWithCreate(function () use ($l, $r) {
            return $l->takeUntil($r);
        });

        $this->assertMessages(
            [
                onNext(210, 2),
                onNext(220, 3),
                onNext(230, 4),
                onNext(240, 5),
                onCompleted(250)
            ],
            $result->getMessages()
        );
    }


    /**
     * @test
     */
    public function takeUntil_preempt_some_data_never()
    {
        $l = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onNext(220, 3),
            onNext(230, 4),
            onNext(240, 5),
            onCompleted(250)
        ]);

        $r = Observable::never();


        $result = $this->scheduler->startWithCreate(function () use ($l, $r) {
            return $l->takeUntil($r);
        });

        $this->assertMessages(
            [
                onNext(210, 2),
                onNext(220, 3),
                onNext(230, 4),
                onNext(240, 5),
                onCompleted(250)
            ],
            $result->getMessages()
        );
    }

    /**
     * @test
     */
    public function takeUntil_preempt_never_next()
    {
        $l = Observable::never();

        $r = $this->createHotObservable([
            onNext(150, 1),
            onNext(225, 2),
            onCompleted(250)
        ]);

        $result = $this->scheduler->startWithCreate(function () use ($l, $r) {
            return $l->takeUntil($r);
        });

        $this->assertMessages(
            [
                onCompleted(225)
            ],
            $result->getMessages()
        );
    }

    /**
     * @test
     */
    public function takeUntil_preempt_never_error()
    {

        $error = new \Exception();

        $l = Observable::never();

        $r = $this->createHotObservable([
            onNext(150, 1),
            onError(225, $error)

        ]);

        $result = $this->scheduler->startWithCreate(function () use ($l, $r) {
            return $l->takeUntil($r);
        });

        $this->assertMessages(
            [
                onError(225, $error)
            ],
            $result->getMessages()
        );
    }

    /**
     * @test
     */
    public function takeUntil_preempt_never_empty()
    {
        $l = Observable::never();

        $r = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(225)
        ]);

        $result = $this->scheduler->startWithCreate(function () use ($l, $r) {
            return $l->takeUntil($r);
        });

        $this->assertMessages([], $result->getMessages());
    }

    /**
     * @test
     */
    public function takeUntil_preempt_never_never()
    {
        $l = Observable::never();

        $r = Observable::never();

        $result = $this->scheduler->startWithCreate(function () use ($l, $r) {
            return $l->takeUntil($r);
        });

        $this->assertMessages([], $result->getMessages());
    }

    /**
     * @test
     */
    public function takeUntil_before_first_produced()
    {
        $l = $this->createHotObservable([
            onNext(150, 1),
            onNext(230, 2),
            onCompleted(240)
        ]);

        $r = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onCompleted(220)
        ]);


        $result = $this->scheduler->startWithCreate(function () use ($l, $r) {
            return $l->takeUntil($r);
        });

        $this->assertMessages(
            [
                onCompleted(210)
            ],
            $result->getMessages()
        );
    }

    /**
     * @test
     */
    public function takeUntil_before_first_produced_remain_silent_and_proper_disposed()
    {

        $sourceNotDisposed = false;

        $l = $this->createHotObservable([
            onNext(150, 1),
            onError(215, new \Exception()),
            onCompleted(240)
        ])->doOnNext(function () use (&$sourceNotDisposed) {
            $sourceNotDisposed = true;
        });

        $r = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onCompleted(220)
        ]);


        $result = $this->scheduler->startWithCreate(function () use ($l, $r) {
            return $l->takeUntil($r);
        });

        $this->assertMessages(
            [
                onCompleted(210)
            ],
            $result->getMessages()
        );

        $this->assertFalse($sourceNotDisposed);
    }


    /**
     * @test
     */
    public function takeUntil_no_preempt_after_last_produced_proper_disposed_signal()
    {

        $sourceNotDisposed = false;

        $l = $this->createHotObservable([
            onNext(150, 1),
            onNext(230, 2),
            onCompleted(240)
        ]);

        $r = $this->createHotObservable([
            onNext(150, 1),
            onNext(250, 2),
            onCompleted(260)
        ])->doOnNext(function () use (&$sourceNotDisposed) {
            $sourceNotDisposed = true;
        });


        $result = $this->scheduler->startWithCreate(function () use ($l, $r) {
            return $l->takeUntil($r);
        });

        $this->assertMessages(
            [
                onNext(230, 2),
                onCompleted(240)
            ],
            $result->getMessages()
        );

        $this->assertFalse($sourceNotDisposed);
    }
}
