<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable;

class CountTest extends FunctionalTestCase
{
    public function testCountEmpty()
    {

        $xs = $this->createHotObservable(
            [
                onNext(150, 1),
                onCompleted(250)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->count();
        });

        $this->assertMessages(
            [
                onNext(250, 0),
                onCompleted(250)
            ],
            $results->getMessages()
        );
    }

    public function testCountSome()
    {
        $xs = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(210, 2),
                onNext(220, 3),
                onNext(230, 4),
                onCompleted(250)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->count();
        });

        $this->assertMessages(
            [
                onNext(250, 3),
                onCompleted(250)
            ],
            $results->getMessages()
        );
    }

    public function testCountThrow()
    {

        $xs = $this->createHotObservable(
            [
                onNext(150, 1),
                onError(210, new \Exception())
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->count();
        });

        $this->assertMessages([onError(210, new \Exception())], $results->getMessages());
    }

    public function testCountNever()
    {

        $xs = $this->createHotObservable(
            [
                onNext(150, 1)
            ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->count();
        });

        $this->assertMessages([], $results->getMessages());
    }

    public function testCountPredicateEmptyTrue()
    {

        $xs = $this->createHotObservable(
            [
                onNext(150, 1),
                onCompleted(250)
            ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->count(function () {
                return true;
            });
        });

        $this->assertMessages(
            [
                onNext(250, 0),
                onCompleted(250)
            ],
            $results->getMessages());

        $this->assertSubscriptions([subscribe(200, 250)], $xs->getSubscriptions());
    }

    public function testCountPredicateEmptyFalse()
    {

        $xs = $this->createHotObservable(
            [
                onNext(150, 1),
                onCompleted(250)
            ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->count(function () {
                return false;
            });
        });

        $this->assertMessages(
            [
                onNext(250, 0),
                onCompleted(250)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions([subscribe(200, 250)], $xs->getSubscriptions());
    }

    public function testCountPredicateReturnTrue()
    {

        $xs = $this->createHotObservable([onNext(150, 1), onNext(210, 2), onCompleted(250)]);
        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->count(function () {
                return true;
            });
        });
        $this->assertMessages([onNext(250, 1), onCompleted(250)], $results->getMessages());
        $this->assertSubscriptions([subscribe(200, 250)], $xs->getSubscriptions());
    }

    public function testCountPredicateReturnFalse()
    {

        $xs = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(210, 2),
                onCompleted(250)
            ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->count(function () {
                return false;
            });
        });

        $this->assertMessages(
            [
                onNext(250, 0),
                onCompleted(250)
            ],
            $results->getMessages());

        $this->assertSubscriptions([subscribe(200, 250)], $xs->getSubscriptions());
    }

    public function testCountPredicateAllMatched()
    {

        $xs = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(210, 2),
                onNext(220, 3),
                onNext(230, 4),
                onCompleted(250)
            ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->count(function ($x) {
                return $x < 10;
            });
        });

        $this->assertMessages(
            [
                onNext(250, 3),
                onCompleted(250)
            ],
            $results->getMessages());

        $this->assertSubscriptions([subscribe(200, 250)], $xs->getSubscriptions());
    }

    public function testCountPredicateNoneMatched()
    {

        $xs = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(210, 2),
                onNext(220, 3),
                onNext(230, 4),
                onCompleted(250)
            ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->count(function ($x) {
                return $x > 10;
            });
        });

        $this->assertMessages(
            [
                onNext(250, 0),
                onCompleted(250)
            ],
            $results->getMessages());

        $this->assertSubscriptions([subscribe(200, 250)], $xs->getSubscriptions());
    }

    public function testCountPredicateSomeEven()
    {

        $xs = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(210, 2),
                onNext(220, 3),
                onNext(230, 4),
                onCompleted(250)]
        );

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->count(function ($x) {
                return $x % 2 === 0;
            });
        });

        $this->assertMessages(
            [onNext(250, 2), onCompleted(250)],
            $results->getMessages()
        );

        $this->assertSubscriptions([subscribe(200, 250)], $xs->getSubscriptions());
    }

    public function testCountPredicateThrowTrue()
    {

        $xs = $this->createHotObservable(
            [
                onNext(150, 1),
                onError(210, new \Exception())
            ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->count(function () {
                return true;
            });
        });

        $this->assertMessages([onError(210, new \Exception())], $results->getMessages());

        $this->assertSubscriptions([subscribe(200, 210)], $xs->getSubscriptions());
    }

    public function testCountPredicateThrowFalse()
    {

        $xs = $this->createHotObservable(
            [
                onNext(150, 1),
                onError(210, new \Exception())]
        );

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->count(function () {
                return false;
            });
        });

        $this->assertMessages([onError(210, new \Exception())], $results->getMessages());

        $this->assertSubscriptions([subscribe(200, 210)], $xs->getSubscriptions());
    }

    public function testCountPredicateNever()
    {

        $xs = $this->createHotObservable(
            [
                onNext(150, 1)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->count(function () {
                return true;
            });
        });

        $this->assertMessages([], $results->getMessages());

        $this->assertSubscriptions([subscribe(200, 1000)], $xs->getSubscriptions());
    }

    public function testCountPredicateThrowsError()
    {

        $xs = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(210, 2),
                onNext(230, 3),
                onCompleted(240)
            ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->count(function ($x) {
                if ($x === 3) {
                    throw new \Exception();
                }
                return true;
            });
        });

        $this->assertMessages([onError(230, new \Exception())], $results->getMessages());

        $this->assertSubscriptions([subscribe(200, 230)], $xs->getSubscriptions());
    }

    public function testCountAfterRange()
    {

        $xs = Observable::fromArray(range(1, 10), $this->scheduler);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->count();
        });

        $this->assertMessages([onNext(211, 10), onCompleted(211)], $results->getMessages());
    }

}