<?php

declare(strict_types = 1);


namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Testing\MockObserver;

class SkipLastTest extends FunctionalTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSkipLastNegative()
    {
        $xs = $this->createHotObservable(
            [
                onNext(180, 1),
                onNext(210, 2),
                onNext(250, 3),
                onNext(270, 4),
                onNext(310, 5),
                onNext(360, 6),
                onNext(380, 7),
                onNext(410, 8),
                onNext(590, 9),
                onCompleted(650)
            ]
        );

        $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->skipLast(-1);
        });

    }

    public function testSkipLastZeroCompleted()
    {

        $xs = $this->createHotObservable(
            [
                onNext(180, 1),
                onNext(210, 2),
                onNext(250, 3),
                onNext(270, 4),
                onNext(310, 5),
                onNext(360, 6),
                onNext(380, 7),
                onNext(410, 8),
                onNext(590, 9),
                onCompleted(650)
            ]
        );

        /** @var MockObserver $results */
        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->skipLast(0);
        });
        $this->assertMessages(
            [
                onNext(210, 2),
                onNext(250, 3),
                onNext(270, 4),
                onNext(310, 5),
                onNext(360, 6),
                onNext(380, 7),
                onNext(410, 8),
                onNext(590, 9),
                onCompleted(650)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 650)
            ],
            $xs->getSubscriptions()
        );
    }

    public function testSkipLastZeroError()
    {

        $ex = new \Exception('ex');
        $xs = $this->createHotObservable(
            [
                onNext(180, 1),
                onNext(210, 2),
                onNext(250, 3),
                onNext(270, 4),
                onNext(310, 5),
                onNext(360, 6),
                onNext(380, 7),
                onNext(410, 8),
                onNext(590, 9),
                onError(650, $ex)
            ]
        );
        /** @var MockObserver $results */
        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->skipLast(0);
        });
        $this->assertMessages(
            [
                onNext(210, 2),
                onNext(250, 3),
                onNext(270, 4),
                onNext(310, 5),
                onNext(360, 6),
                onNext(380, 7),
                onNext(410, 8),
                onNext(590, 9),
                onError(650, $ex)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 650)
            ],
            $xs->getSubscriptions()
        );
    }

    public function testSkipLastZeroDisposed()
    {

        $xs = $this->createHotObservable(
            [
                onNext(180, 1),
                onNext(210, 2),
                onNext(250, 3),
                onNext(270, 4),
                onNext(310, 5),
                onNext(360, 6),
                onNext(380, 7),
                onNext(410, 8),
                onNext(590, 9)
            ]
        );
        /** @var MockObserver $results */
        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->skipLast(0);
        });

        $this->assertMessages(
            [
                onNext(210, 2),
                onNext(250, 3),
                onNext(270, 4),
                onNext(310, 5),
                onNext(360, 6),
                onNext(380, 7),
                onNext(410, 8),
                onNext(590, 9)
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

    public function testSkipLastOneCompleted()
    {

        $xs = $this->createHotObservable(
            [
                onNext(180, 1),
                onNext(210, 2),
                onNext(250, 3),
                onNext(270, 4),
                onNext(310, 5),
                onNext(360, 6),
                onNext(380, 7),
                onNext(410, 8),
                onNext(590, 9),
                onCompleted(650)
            ]
        );
        /** @var MockObserver $results */
        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->skipLast(1);
        });

        $this->assertMessages(
            [
                onNext(250, 2),
                onNext(270, 3),
                onNext(310, 4),
                onNext(360, 5),
                onNext(380, 6),
                onNext(410, 7),
                onNext(590, 8),
                onCompleted(650)

            ],
            $results->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 650)
            ],
            $xs->getSubscriptions()
        );
    }

    public function testSkipLastOneError()
    {

        $ex = new \Exception('ex');

        $xs = $this->createHotObservable(
            [
                onNext(180, 1),
                onNext(210, 2),
                onNext(250, 3),
                onNext(270, 4),
                onNext(310, 5),
                onNext(360, 6),
                onNext(380, 7),
                onNext(410, 8),
                onNext(590, 9),
                onError(650, $ex)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->skipLast(1);
        });

        $this->assertMessages(
            [
                onNext(250, 2),
                onNext(270, 3),
                onNext(310, 4),
                onNext(360, 5),
                onNext(380, 6),
                onNext(410, 7),
                onNext(590, 8),
                onError(650, $ex)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 650)
            ],
            $xs->getSubscriptions()
        );
    }

    public function testSkipLastOneDisposed()
    {

        $xs = $this->createHotObservable(
            [
                onNext(180, 1),
                onNext(210, 2),
                onNext(250, 3),
                onNext(270, 4),
                onNext(310, 5),
                onNext(360, 6),
                onNext(380, 7),
                onNext(410, 8),
                onNext(590, 9)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->skipLast(1);
        });

        $this->assertMessages(
            [
                onNext(250, 2),
                onNext(270, 3),
                onNext(310, 4),
                onNext(360, 5),
                onNext(380, 6),
                onNext(410, 7),
                onNext(590, 8)
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

    public function testSkipLastThreeCompleted()
    {

        $xs = $this->createHotObservable(
            [
                onNext(180, 1),
                onNext(210, 2),
                onNext(250, 3),
                onNext(270, 4),
                onNext(310, 5),
                onNext(360, 6),
                onNext(380, 7),
                onNext(410, 8),
                onNext(590, 9),
                onCompleted(650)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->skipLast(3);
        });

        $this->assertMessages(
            [
                onNext(310, 2),
                onNext(360, 3),
                onNext(380, 4),
                onNext(410, 5),
                onNext(590, 6),
                onCompleted(650)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 650)
            ],
            $xs->getSubscriptions()
        );
    }

    public function testSkipLastThreeError()
    {

        $ex = new \Exception('ex');

        $xs = $this->createHotObservable(
            [
                onNext(180, 1),
                onNext(210, 2),
                onNext(250, 3),
                onNext(270, 4),
                onNext(310, 5),
                onNext(360, 6),
                onNext(380, 7),
                onNext(410, 8),
                onNext(590, 9),
                onError(650, $ex)
            ]
        );
        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->skipLast(3);
        });

        $this->assertMessages(
            [
                onNext(310, 2),
                onNext(360, 3),
                onNext(380, 4),
                onNext(410, 5),
                onNext(590, 6),
                onError(650, $ex)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 650)
            ],
            $xs->getSubscriptions()
        );
    }

    public function testSkipLastThreeDisposed()
    {

        $xs = $this->createHotObservable(
            [
                onNext(180, 1),
                onNext(210, 2),
                onNext(250, 3),
                onNext(270, 4),
                onNext(310, 5),
                onNext(360, 6),
                onNext(380, 7),
                onNext(410, 8),
                onNext(590, 9)
            ]
        );
        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->skipLast(3);
        });

        $this->assertMessages(
            [
                onNext(310, 2),
                onNext(360, 3),
                onNext(380, 4),
                onNext(410, 5),
                onNext(590, 6)
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
}
