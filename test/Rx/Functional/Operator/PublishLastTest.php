<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable;
use Rx\Observable\NeverObservable;

class PublishLastTest extends FunctionalTestCase
{

    public function add($x, $y)
    {
        return $x + $y;
    }

    /**
     * @test
     */
    public function publishLast_basic()
    {

        $xs = $this->createHotObservable([
          onNext(110, 7),
          onNext(220, 3),
          onNext(280, 4),
          onNext(290, 1),
          onNext(340, 8),
          onNext(360, 5),
          onNext(370, 6),
          onNext(390, 7),
          onNext(410, 13),
          onNext(430, 2),
          onNext(450, 9),
          onNext(520, 11),
          onNext(560, 20),
          onCompleted(600)
        ]);


        $ys           = null;
        $subscription = null;
        $connection   = null;

        $results = $this->scheduler->createObserver();

        $this->scheduler->scheduleAbsolute(100, function () use (&$ys, $xs) {
            $ys = $xs->publishLast();
        });

        $this->scheduler->scheduleAbsolute(200, function () use (&$ys, $xs, $results, &$subscription) {
            $subscription = $ys->subscribe($results);
        });

        $this->scheduler->scheduleAbsolute(1000, function () use (&$subscription) {
            $subscription->dispose();
        });

        $this->scheduler->scheduleAbsolute(300, function () use (&$connection, &$ys) {
            $connection = $ys->connect();
        });

        $this->scheduler->scheduleAbsolute(400, function () use (&$connection) {
            $connection->dispose();
        });

        $this->scheduler->scheduleAbsolute(500, function () use (&$connection, &$ys) {
            $connection = $ys->connect();
        });

        $this->scheduler->scheduleAbsolute(550, function () use (&$connection) {
            $connection->dispose();
        });

        $this->scheduler->scheduleAbsolute(650, function () use (&$connection, &$ys) {
            $connection = $ys->connect();
        });

        $this->scheduler->scheduleAbsolute(800, function () use (&$connection) {
            $connection->dispose();
        });


        $this->scheduler->start();

        $this->assertMessages([], $results->getMessages());


        $this->assertSubscriptions([
          subscribe(300, 400),
          subscribe(500, 550),
          subscribe(650, 800)
        ],
          $xs->getSubscriptions()
        );
    }


    /**
     * @test
     */
    public function publishLast_error()
    {

        $error = new \Exception();

        $xs = $this->createHotObservable([
          onNext(110, 7),
          onNext(220, 3),
          onNext(280, 4),
          onNext(290, 1),
          onNext(340, 8),
          onNext(360, 5),
          onNext(370, 6),
          onNext(390, 7),
          onNext(410, 13),
          onNext(430, 2),
          onNext(450, 9),
          onNext(520, 11),
          onNext(560, 20),
          onError(600, $error)
        ]);


        $ys           = null;
        $subscription = null;
        $connection   = null;

        $results = $this->scheduler->createObserver();

        $this->scheduler->scheduleAbsolute(100, function () use (&$ys, $xs) {
            $ys = $xs->publishLast();
        });

        $this->scheduler->scheduleAbsolute(200, function () use (&$ys, $xs, $results, &$subscription) {
            $subscription = $ys->subscribe($results);
        });

        $this->scheduler->scheduleAbsolute(1000, function () use (&$subscription) {
            $subscription->dispose();
        });

        $this->scheduler->scheduleAbsolute(300, function () use (&$connection, &$ys) {
            $connection = $ys->connect();
        });

        $this->scheduler->scheduleAbsolute(400, function () use (&$connection) {
            $connection->dispose();
        });

        $this->scheduler->scheduleAbsolute(500, function () use (&$connection, &$ys) {
            $connection = $ys->connect();
        });

        $this->scheduler->scheduleAbsolute(800, function () use (&$connection) {
            $connection->dispose();
        });


        $this->scheduler->start();

        $this->assertMessages([
          onError(600, $error)
        ],
          $results->getMessages()
        );


        $this->assertSubscriptions([
          subscribe(300, 400),
          subscribe(500, 600)
        ],
          $xs->getSubscriptions()
        );
    }

    /**
     * @test
     */
    public function publishLast_complete()
    {

        $xs = $this->createHotObservable([
          onNext(110, 7),
          onNext(220, 3),
          onNext(280, 4),
          onNext(290, 1),
          onNext(340, 8),
          onNext(360, 5),
          onNext(370, 6),
          onNext(390, 7),
          onNext(410, 13),
          onNext(430, 2),
          onNext(450, 9),
          onNext(520, 11),
          onNext(560, 20),
          onCompleted(600)
        ]);


        $ys           = null;
        $subscription = null;
        $connection   = null;

        $results = $this->scheduler->createObserver();

        $this->scheduler->scheduleAbsolute(100, function () use (&$ys, $xs) {
            $ys = $xs->publishLast();
        });

        $this->scheduler->scheduleAbsolute(200, function () use (&$ys, $xs, $results, &$subscription) {
            $subscription = $ys->subscribe($results);
        });

        $this->scheduler->scheduleAbsolute(1000, function () use (&$subscription) {
            $subscription->dispose();
        });

        $this->scheduler->scheduleAbsolute(300, function () use (&$connection, &$ys) {
            $connection = $ys->connect();
        });

        $this->scheduler->scheduleAbsolute(400, function () use (&$connection) {
            $connection->dispose();
        });

        $this->scheduler->scheduleAbsolute(500, function () use (&$connection, &$ys) {
            $connection = $ys->connect();
        });

        $this->scheduler->scheduleAbsolute(800, function () use (&$connection) {
            $connection->dispose();
        });


        $this->scheduler->start();

        $this->assertMessages([
          onNext(600, 20),
          onCompleted(600)
        ],
          $results->getMessages()
        );


        $this->assertSubscriptions([
          subscribe(300, 400),
          subscribe(500, 600)
        ],
          $xs->getSubscriptions()
        );
    }


    /**
     * @test
     */
    public function publishLast_dispose()
    {

        $xs = $this->createHotObservable([
          onNext(110, 7),
          onNext(220, 3),
          onNext(280, 4),
          onNext(290, 1),
          onNext(340, 8),
          onNext(360, 5),
          onNext(370, 6),
          onNext(390, 7),
          onNext(410, 13),
          onNext(430, 2),
          onNext(450, 9),
          onNext(520, 11),
          onNext(560, 20),
          onCompleted(600)
        ]);


        $ys           = null;
        $subscription = null;
        $connection   = null;

        $results = $this->scheduler->createObserver();

        $this->scheduler->scheduleAbsolute(100, function () use (&$ys, $xs) {
            $ys = $xs->publishLast();
        });

        $this->scheduler->scheduleAbsolute(200, function () use (&$ys, $xs, $results, &$subscription) {
            $subscription = $ys->subscribe($results);
        });

        $this->scheduler->scheduleAbsolute(350, function () use (&$subscription) {
            $subscription->dispose();
        });

        $this->scheduler->scheduleAbsolute(300, function () use (&$connection, &$ys) {
            $connection = $ys->connect();
        });

        $this->scheduler->scheduleAbsolute(400, function () use (&$connection) {
            $connection->dispose();
        });

        $this->scheduler->scheduleAbsolute(500, function () use (&$connection, &$ys) {
            $connection = $ys->connect();
        });

        $this->scheduler->scheduleAbsolute(550, function () use (&$connection) {
            $connection->dispose();
        });

        $this->scheduler->scheduleAbsolute(650, function () use (&$connection, &$ys) {
            $connection = $ys->connect();
        });

        $this->scheduler->scheduleAbsolute(800, function () use (&$connection) {
            $connection->dispose();
        });


        $this->scheduler->start();

        $this->assertMessages([], $results->getMessages());


        $this->assertSubscriptions([
          subscribe(300, 400),
          subscribe(500, 550),
          subscribe(650, 800)
        ],
          $xs->getSubscriptions()
        );
    }

    /**
     * @test
     */
    public function publishLast_multiple_connections()
    {
        $xs = new NeverObservable();
        $ys = $xs->publishLast();

        $connection1 = $ys->connect();
        $connection2 = $ys->connect();

        $this->assertTrue($connection1 === $connection2);

        $connection1->dispose();
        $connection2->dispose();

        $connection3 = $ys->connect();

        $this->assertTrue($connection1 !== $connection3);

    }

    /**
     * @test
     */
    public function publishLast_zip_complete()
    {

        $xs = $this->createHotObservable([
          onNext(110, 7),
          onNext(220, 3),
          onNext(280, 4),
          onNext(290, 1),
          onNext(340, 8),
          onNext(360, 5),
          onNext(370, 6),
          onNext(390, 7),
          onNext(410, 13),
          onNext(430, 2),
          onNext(450, 9),
          onNext(520, 11),
          onNext(560, 20),
          onCompleted(600)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->publishLast(function (Observable $ys) {
                return $ys->zip([$ys], [$this, 'add']);
            });
        });

        $this->assertMessages([
          onNext(600, 40),
          onCompleted(600)
        ],
          $results->getMessages()
        );

        $this->assertSubscriptions([
          subscribe(200, 600)
        ],
          $xs->getSubscriptions()
        );
    }

    /**
     * @test
     */
    public function publishLast_zip_errorr()
    {

        $error = new \Exception();

        $xs = $this->createHotObservable([
          onNext(110, 7),
          onNext(220, 3),
          onNext(280, 4),
          onNext(290, 1),
          onNext(340, 8),
          onNext(360, 5),
          onNext(370, 6),
          onNext(390, 7),
          onNext(410, 13),
          onNext(430, 2),
          onNext(450, 9),
          onNext(520, 11),
          onNext(560, 20),
          onError(600, $error)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->publishLast(function (Observable $ys) {
                return $ys->zip([$ys], [$this, 'add']);
            });
        });

        $this->assertMessages([
          onError(600, $error)
        ],
          $results->getMessages()
        );

        $this->assertSubscriptions([
          subscribe(200, 600)
        ],
          $xs->getSubscriptions()
        );
    }

    /**
     * @test
     */
    public function publishLast_zip_dispose()
    {

        $xs = $this->createHotObservable([
            onNext(110, 7),
            onNext(220, 3),
            onNext(280, 4),
            onNext(290, 1),
            onNext(340, 8),
            onNext(360, 5),
            onNext(370, 6),
            onNext(390, 7),
            onNext(410, 13),
            onNext(430, 2),
            onNext(450, 9),
            onNext(520, 11),
            onNext(560, 20),
            onCompleted(600)
        ]);

        $results = $this->scheduler->startWithDispose(function () use ($xs) {
            return $xs->publishLast(function (Observable $ys)  {
                return $ys->zip([$ys], [$this, 'add']);
            });
        }, 470);

        $this->assertMessages([], $results->getMessages());

        $this->assertSubscriptions([
          subscribe(200, 470)
        ],
          $xs->getSubscriptions()
        );
    }
}
