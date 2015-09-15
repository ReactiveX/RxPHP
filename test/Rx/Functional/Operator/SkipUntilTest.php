<?php


namespace Rx\Functional\Operator;

use Rx\Disposable\CallbackDisposable;
use Rx\Functional\FunctionalTestCase;
use Rx\Observable\AnonymousObservable;
use Rx\Observable\BaseObservable;
use Rx\Testing\MockObserver;

class SkipUntilTest extends FunctionalTestCase
{
    public function testSkipUntilSomeDataNext()
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

    public function testSkipUntilSomeDataError()
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

    public function testSkipUntilSomeDataEmpty()
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

    public function testSkipUntilNeverNext()
    {

        $l = BaseObservable::never();

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

    public function testSkipUntilNeverError()
    {

        $error = new \Exception();

        $l = BaseObservable::never();

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

    public function testSkipUntilSomeDataNever()
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

        $r = BaseObservable::never();

        $results = $this->scheduler->startWithCreate(function () use ($l, $r) {
            return $l->skipUntil($r);
        });

        $this->assertMessages(
            [],
            $results->getMessages()
        );
    }

    public function testSkipUntilNeverEmpty()
    {

        $l = BaseObservable::never();

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

    public function testSkipUntilNeverNever()
    {

        $l = BaseObservable::never();

        $r = BaseObservable::never();

        $results = $this->scheduler->startWithCreate(function () use ($l, $r) {
            return $l->skipUntil($r);
        });

        $this->assertMessages(
            [],
            $results->getMessages()
        );
    }

    public function testSkipUntilHasCompletedCausesDisposal()
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
            return new CallbackDisposable(function () use (&$disposed) {
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

}