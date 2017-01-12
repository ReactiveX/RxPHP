<?php

declare(strict_types = 1);


namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Testing\MockObserver;


class ToArrayTest extends FunctionalTestCase
{
    public function testToArrayCompleted()
    {

        $xs = $this->createHotObservable(
            [
                onNext(110, 1),
                onNext(220, 2),
                onNext(330, 3),
                onNext(440, 4),
                onNext(550, 5),
                onCompleted(660)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->toArray();
        });

        $this->assertMessages(
            [
                onNext(660, [2, 3, 4, 5]),
                onCompleted(660)
            ],
            $results->getMessages()
        );

    }

    public function testToArrayError()
    {
        $error = new \Exception();


        $xs = $this->createHotObservable(
            [
                onNext(110, 1),
                onNext(220, 2),
                onNext(330, 3),
                onNext(440, 4),
                onNext(550, 5),
                onError(660, $error)
            ]
        );
        /** @var MockObserver $results */
        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->toArray();
        });

        $this->assertMessages(
            [
                onError(660, $error)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 660)
            ],
            $xs->getSubscriptions()
        );
    }

    public function testToArrayDisposed()
    {

        $xs = $this->createHotObservable(
            [
                onNext(110, 1),
                onNext(220, 2),
                onNext(330, 3),
                onNext(440, 4),
                onNext(550, 5)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->toArray();
        });

        $this->assertMessages([], $results->getMessages());

        $this->assertSubscriptions(
            [
                subscribe(200, 1000)
            ],
            $xs->getSubscriptions()
        );
    }
}