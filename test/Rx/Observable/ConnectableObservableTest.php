<?php

declare(strict_types = 1);

namespace Rx\Observable;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable;
use Rx\Observer\CallbackObserver;
use Rx\Subject\Subject;
use Rx\Testing\TestSubject;

class ConnectableObservableTest extends FunctionalTestCase
{

    /**
     * @test
     */
    public function connectable_observable_creation()
    {
        $y   = 0;
        $s2  = new Subject();
        $co2 = new ConnectableObservable(Observable::of(1), $s2);

        $co2->subscribe(new CallbackObserver(function ($x) use (&$y) {
            $y = $x;
        }));

        $this->assertNotEquals(1, $y);

        $co2->connect();

        $this->assertEquals(1, $y);
    }


    /**
     * @test
     */
    public function connectable_observable_connected()
    {
        $xs = $this->createHotObservable(
          [
            onNext(210, 1),
            onNext(220, 2),
            onNext(230, 3),
            onNext(240, 4),
            onCompleted(250)
          ]);

        $subject = new TestSubject();

        $conn = new ConnectableObservable($xs, $subject);
        $conn->connect();

        $results = $this->scheduler->startWithCreate(function () use ($xs, $conn) {
            return $conn;
        });

        $this->assertMessages(
          [
            onNext(210, 1),
            onNext(220, 2),
            onNext(230, 3),
            onNext(240, 4),
            onCompleted(250)
          ],
          $results->getMessages()
        );
    }


    /**
     * @test
     */
    public function connectable_observable_disconnected()
    {
        $xs = $this->createHotObservable(
          [
            onNext(210, 1),
            onNext(220, 2),
            onNext(230, 3),
            onNext(240, 4),
            onCompleted(250)
          ]);

        $subject = new TestSubject();

        $conn       = new ConnectableObservable($xs, $subject);
        $disconnect = $conn->connect();
        $disconnect->dispose();

        $results = $this->scheduler->startWithCreate(function () use ($xs, $conn) {
            return $conn;
        });

        $this->assertMessages([], $results->getMessages());

    }

    /**
     * @test
     */
    public function connectable_observable_disconnect_future()
    {
        $xs = $this->createHotObservable(
          [
            onNext(210, 1),
            onNext(220, 2),
            onNext(230, 3),
            onNext(240, 4),
            onCompleted(250)
          ]);

        $subject = new TestSubject();

        $conn = new ConnectableObservable($xs, $subject);
        $subject->disposeOn(3, $conn->connect());

        $results = $this->scheduler->startWithCreate(function () use ($xs, $conn) {
            return $conn;
        });

        $this->assertMessages([
          onNext(210, 1),
          onNext(220, 2),
          onNext(230, 3)
        ],
          $results->getMessages()
        );

    }

    /**
     * @test
     */
    public function connectable_observable_multiple_non_overlapped_connections()
    {
        $xs = $this->createHotObservable(
          [
            onNext(210, 1),
            onNext(220, 2),
            onNext(230, 3),
            onNext(240, 4),
            onNext(250, 5),
            onNext(260, 6),
            onNext(270, 7),
            onNext(280, 8),
            onNext(290, 9),
            onCompleted(300)
          ]);

        $subject = new TestSubject();

        $conn = $xs->multicast($subject);

        $c1 = null;
        $this->scheduler->scheduleAbsolute(225, function () use(&$c1, $conn){ $c1 = $conn->connect(); });
        $this->scheduler->scheduleAbsolute(241, function () use(&$c1){ $c1->dispose(); });
        $this->scheduler->scheduleAbsolute(245, function () use(&$c1){ $c1->dispose(); }); // idempotency test
        $this->scheduler->scheduleAbsolute(251, function () use(&$c1){ $c1->dispose(); }); // idempotency test
        $this->scheduler->scheduleAbsolute(260, function () use(&$c1){ $c1->dispose(); }); // idempotency test

        $c2 = null;
        $this->scheduler->scheduleAbsolute(249, function () use(&$c2, $conn){ $c2 = $conn->connect(); });
        $this->scheduler->scheduleAbsolute(255, function () use(&$c2){ $c2->dispose(); });
        $this->scheduler->scheduleAbsolute(265, function () use(&$c2){ $c2->dispose(); }); // idempotency test
        $this->scheduler->scheduleAbsolute(280, function () use(&$c2){ $c2->dispose(); }); // idempotency test

        $c3 = null;
        $this->scheduler->scheduleAbsolute(275, function () use(&$c3, $conn){ $c3 = $conn->connect(); });
        $this->scheduler->scheduleAbsolute(295, function () use(&$c3){ $c3->dispose(); });


        $results = $this->scheduler->startWithCreate(function () use ($xs, $conn) {
            return $conn;
        });

        $this->assertMessages([
          onNext(230, 3),
          onNext(240, 4),
          onNext(250, 5),
          onNext(280, 8),
          onNext(290, 9)
        ],
          $results->getMessages()
        );

        $this->assertSubscriptions([
          subscribe(225, 241),
          subscribe(249, 255),
          subscribe(275, 295)
        ],
          $xs->getSubscriptions()
        );
    }
}
