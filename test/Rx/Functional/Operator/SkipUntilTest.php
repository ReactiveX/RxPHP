<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Rx\Disposable\CallbackDisposable;
use Rx\Functional\FunctionalTestCase;
use Rx\Observable\AnonymousObservable;
use Rx\Observable;
use Rx\Testing\MockObserver;

class SkipUntilTest extends FunctionalTestCase
{
    public function testSkipUntilSomeDataNext(): void
    {

        $l = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(210, 2),
                onNext(220, 3),
                onNext(230, 4),
                onNext(240, 5),
                onCompleted(250)
            ]
        );

        $r = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(225, 99),
                onCompleted(230)
            ]
        );

        /** @var MockObserver $results */
        $results = $this->scheduler->startWithCreate(function () use ($l, $r) {
            return $l->skipUntil($r);
        });
        $this->assertMessages(
            [
                onNext(230, 4),
                onNext(240, 5),
                onCompleted(250)
            ],

            $results->getMessages()
        );
    }

    public function testSkipUntilSomeDataError(): void
    {

        $error = new \Exception();

        $l = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(210, 2),
                onNext(220, 3),
                onNext(230, 4),
                onNext(240, 5),
                onCompleted(250)
            ]
        );

        $r = $this->createHotObservable(
            [
                onNext(150, 1),
                onError(225, $error)
            ]
        );
        /** @var MockObserver $results */
        $results = $this->scheduler->startWithCreate(function () use ($l, $r) {
            return $l->skipUntil($r);
        });

        $this->assertMessages(
            [onError(225, $error)],

            $results->getMessages()
        );
    }

    public function testSkipUntilSomeDataEmpty(): void
    {

        $l = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(210, 2),
                onNext(220, 3),
                onNext(230, 4),
                onNext(240, 5),
                onCompleted(250)
            ]
        );

        $r = $this->createHotObservable(
            [
                onNext(150, 1),
                onCompleted(225)
            ]
        );

        /** @var MockObserver $results */
        $results = $this->scheduler->startWithCreate(function () use ($l, $r) {
            return $l->skipUntil($r);
        });

        $this->assertMessages(
            [],

            $results->getMessages()
        );

    }

    public function testSkipUntilNeverNext(): void
    {

        $l = Observable::never();

        $r = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(225, 2),
                onCompleted(250)
            ]
        );

        /** @var MockObserver $results */
        $results = $this->scheduler->startWithCreate(function () use ($l, $r) {
            return $l->skipUntil($r);
        });

        $this->assertMessages(
            [],

            $results->getMessages()
        );
    }

    public function testSkipUntilNeverError(): void
    {

        $error = new \Exception();

        $l = Observable::never();

        $r = $this->createHotObservable(
            [
                onNext(150, 1),
                onError(225, $error)
            ]
        );

        /** @var MockObserver $results */
        $results = $this->scheduler->startWithCreate(function () use ($l, $r) {
            return $l->skipUntil($r);
        });

        $this->assertMessages(
            [onError(225, $error)],

            $results->getMessages()
        );
    }

    public function testSkipUntilSomeDataNever(): void
    {

        $l = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(210, 2),
                onNext(220, 3),
                onNext(230, 4),
                onNext(240, 5),
                onCompleted(250)
            ]
        );

        $r = Observable::never();

        $results = $this->scheduler->startWithCreate(function () use ($l, $r) {
            return $l->skipUntil($r);
        });

        $this->assertMessages(
            [],
            $results->getMessages()
        );
    }

    public function testSkipUntilNeverEmpty(): void
    {

        $l = Observable::never();

        $r = $this->createHotObservable(
            [
                onNext(150, 1),
                onCompleted(225)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($l, $r) {
            return $l->skipUntil($r);
        });

        $this->assertMessages(
            [],
            $results->getMessages()
        );
    }

    public function testSkipUntilNeverNever(): void
    {

        $l = Observable::never();

        $r = Observable::never();

        $results = $this->scheduler->startWithCreate(function () use ($l, $r) {
            return $l->skipUntil($r);
        });

        $this->assertMessages(
            [],
            $results->getMessages()
        );
    }

    public function testSkipUntilHasCompletedCausesDisposal(): void
    {

        $disposed = false;

        $l = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(210, 2),
                onNext(220, 3),
                onNext(230, 4),
                onNext(240, 5),
                onCompleted(250)
            ]
        );

        $r = new AnonymousObservable(function () use (&$disposed) {
            return new CallbackDisposable(function () use (&$disposed): void {
                $disposed = true;
            });
        });

        $results = $this->scheduler->startWithCreate(function () use ($l, $r) {
            return $l->skipUntil($r);
        });

        $this->assertMessages(
            [],
            $results->getMessages()
        );

        $this->assertTrue($disposed);

    }

    public function testCanCompleteInSubscribeAction(): void
    {
        $completed = false;
        $emitted   = null;

        Observable::of(1)
            ->skipUntil(Observable::of(1))
            ->subscribe(
                function ($x) use (&$emitted): void {
                    if ($emitted !== null) {
                        $this->fail('emitted should be null');
                    }
                    $emitted = $x;
                },
                null,
                function () use (&$completed): void {
                    $completed = true;
                }
            );

        $this->assertTrue($completed);
        $this->assertEquals(1, $emitted);
    }
}