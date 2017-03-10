<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable\ErrorObservable;
use Rx\Exception\TimeoutException;

class TimeoutTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function timeout_in_time()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onNext(230, 3),
            onNext(260, 4),
            onNext(300, 5),
            onNext(350, 6),
            onCompleted(400)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->timeout(500, null, $this->scheduler);
        });

        $this->assertMessages(
            [
                onNext(210, 2),
                onNext(230, 3),
                onNext(260, 4),
                onNext(300, 5),
                onNext(350, 6),
                onCompleted(400)
            ],
            $results->getMessages()
        );
    }

    /**
     * @test
     */
    public function timeout_relative_time_timeout_occurs_with_default_error()
    {
        $xs = $this->createHotObservable([
            onNext(410, 1)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->timeout(200, null, $this->scheduler);
        });

        $this->assertMessages(
            [
                onError(401, new TimeoutException())
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 400)
            ],
            $xs->getSubscriptions()
        );
    }

    /**
     * @test
     */
    public function timeout_relative_time_timeout_occurs_with_custom_error()
    {
        $errObs = new ErrorObservable(new \Exception(), $this->scheduler);

        $xs = $this->createHotObservable([
            onNext(410, 1)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs, $errObs) {
            return $xs->timeout(200, $errObs, $this->scheduler);
        });

        $this->assertMessages(
            [
                onError(401, new \Exception())
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 400)
            ],
            $xs->getSubscriptions()
        );
    }

    // test not ported because we don't support absolute time
    // timeout absolute time timeout occurs with default error
    // timeout absolute time timeout occurs with custom error

    /**
     * @test
     */
    public function timeout_out_of_time()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onNext(230, 3),
            onNext(260, 4),
            onNext(300, 5),
            onNext(350, 6),
            onCompleted(400)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->timeout(205, null, $this->scheduler);
        });

        $this->assertMessages(
            [
                onNext(210, 2),
                onNext(230, 3),
                onNext(260, 4),
                onNext(300, 5),
                onNext(350, 6),
                onCompleted(400)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 400)
            ],
            $xs->getSubscriptions()
        );
    }

    /**
     * @test
     */
    public function timeout_timeout_occurs_1()
    {
        $xs = $this->createHotObservable([
            onNext(70, 1),
            onNext(130, 2),
            onNext(310, 3),
            onNext(400, 4),
            onCompleted(500)
        ]);

        $xy = $this->createColdObservable([
            onNext(50, -1),
            onNext(200, -2),
            onNext(310, -3),
            onCompleted(320)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs, $xy) {
            return $xs->timeout(100, $xy, $this->scheduler);
        });

        $this->assertMessages(
            [
                onNext(350, -1),
                onNext(500, -2),
                onNext(610, -3),
                onCompleted(620)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 300)
            ],
            $xs->getSubscriptions()
        );

        $this->assertSubscriptions(
            [
                subscribe(300, 620)
            ],
            $xy->getSubscriptions()
        );
    }

    /**
     * @test
     */
    public function timeout_timeout_occurs_2()
    {
        $xs = $this->createHotObservable([
            onNext(70, 1),
            onNext(130, 2),
            onNext(240, 3),
            onNext(310, 4),
            onNext(430, 5),
            onCompleted(500)
        ]);

        $xy = $this->createColdObservable([
            onNext(50, -1),
            onNext(200, -2),
            onNext(310, -3),
            onCompleted(320)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs, $xy) {
            return $xs->timeout(100, $xy, $this->scheduler);
        });

        $this->assertMessages(
            [
                onNext(240, 3),
                onNext(310, 4),
                onNext(460, -1),
                onNext(610, -2),
                onNext(720, -3),
                onCompleted(730)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 410)
            ],
            $xs->getSubscriptions()
        );

        $this->assertSubscriptions(
            [
                subscribe(410, 730)
            ],
            $xy->getSubscriptions()
        );
    }

    /**
     * @test
     */
    public function timeout_timeout_occurs_never()
    {
        $xs = $this->createHotObservable([
            onNext(70, 1),
            onNext(130, 2),
            onNext(240, 3),
            onNext(310, 4),
            onNext(430, 5),
            onCompleted(500)
        ]);

        $xy = $this->createColdObservable([]);

        $results = $this->scheduler->startWithCreate(function () use ($xs, $xy) {
            return $xs->timeout(100, $xy, $this->scheduler);
        });

        $this->assertMessages(
            [
                onNext(240, 3),
                onNext(310, 4)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 410)
            ],
            $xs->getSubscriptions()
        );

        $this->assertSubscriptions(
            [
                subscribe(410, 1000)
            ],
            $xy->getSubscriptions()
        );
    }

    /**
     * @test
     */
    public function timeout_timeout_occurs_completed()
    {
        $xs = $this->createHotObservable([
            onCompleted(500)
        ]);

        $ys = $this->createColdObservable([
            onNext(100, -1)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs, $ys) {
            return $xs->timeout(100, $ys, $this->scheduler);
        });

        $this->assertMessages(
            [
                onNext(400, -1)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 300)
            ],
            $xs->getSubscriptions()
        );

        $this->assertSubscriptions(
            [
                subscribe(300, 1000)
            ],
            $ys->getSubscriptions()
        );
    }

    /**
     * @test
     */
    public function timeout_timeout_occurs_Error()
    {
        $xs = $this->createHotObservable([
            onError(500, new \Exception())
        ]);

        $ys = $this->createColdObservable([
            onNext(100, -1)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs, $ys) {
            return $xs->timeout(100, $ys, $this->scheduler);
        });

        $this->assertMessages(
            [
                onNext(400, -1)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 300)
            ],
            $xs->getSubscriptions()
        );

        $this->assertSubscriptions(
            [
                subscribe(300, 1000)
            ],
            $ys->getSubscriptions()
        );
    }

    /**
     * @test
     */
    public function timeout_timeout_does_not_occur_completed()
    {
        $xs = $this->createHotObservable([
            onCompleted(250)
        ]);

        $ys = $this->createColdObservable([
            onNext(100, -1)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs, $ys) {
            return $xs->timeout(100, $ys);
        });

        $this->assertMessages(
            [
                onCompleted(250)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 250)
            ],
            $xs->getSubscriptions()
        );

        $this->assertSubscriptions(
            [
            ],
            $ys->getSubscriptions()
        );
    }

    /**
     * @test
     */
    public function timeout_timeout_does_not_occur_Error()
    {
        $xs = $this->createHotObservable([
            onError(250, new \Exception())
        ]);

        $ys = $this->createColdObservable([
            onNext(100, -1)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs, $ys) {
            return $xs->timeout(100, $ys);
        });

        $this->assertMessages(
            [
                onError(250, new \Exception())
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 250)
            ],
            $xs->getSubscriptions()
        );

        $this->assertSubscriptions(
            [
            ],
            $ys->getSubscriptions()
        );
    }

    /**
     * @test
     */
    public function timeout_timeout_does_not_occur()
    {
        $xs = $this->createHotObservable([
            onNext(70, 1),
            onNext(130, 2),
            onNext(240, 3),
            onNext(320, 4),
            onNext(410, 5),
            onCompleted(500)
        ]);

        $ys = $this->createColdObservable([
            onNext(50, -1),
            onNext(200, -2),
            onNext(310, -3),
            onCompleted(320)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs, $ys) {
            return $xs->timeout(100, $ys);
        });

        $this->assertMessages(
            [
                onNext(240, 3),
                onNext(320, 4),
                onNext(410, 5),
                onCompleted(500)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 500)
            ],
            $xs->getSubscriptions()
        );

        $this->assertSubscriptions(
            [
            ],
            $ys->getSubscriptions()
        );
    }

    // not ported because of absolute time
    // timeout absolute time timeout occurs
    // timeout absolute time timeout does not occur completed
    // timeout absolute time timeout does not occur Error
    // timeout absolute time timeoutOccur 2
    // timeout absolute time timeoutOccur 3

    // not ported because of selectors
    // timeout duration simple never
    // timeout duration simple timeout first
    // timeout duration simple timeout later
    // timeout duration simple timeoutByCompletion
    // timeout duration simple timeoutByCompletion
    // timeout duration simple inner throws
    // timeout duration simple first throws
    // timeout duration simple source throws
}