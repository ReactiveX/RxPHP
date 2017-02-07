<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable;
use Rx\Observable\NeverObservable;

class ReplayTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function replay_count_basic()
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
            $ys = $xs->replay(null, 3, null, $this->scheduler);
        });

        $this->scheduler->scheduleAbsolute(450, function () use (&$ys, $xs, $results, &$subscription) {
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

        $this->assertMessages([
          onNext(451, 5),
          onNext(452, 6),
          onNext(453, 7),
          onNext(521, 11)
        ],
          $results->getMessages()
        );


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
    public function replay_count_error()
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
            $ys = $xs->replay(null, 3, null, $this->scheduler);
        });

        $this->scheduler->scheduleAbsolute(450, function () use (&$ys, $xs, $results, &$subscription) {
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
          onNext(451, 5),
          onNext(452, 6),
          onNext(453, 7),
          onNext(521, 11),
          onNext(561, 20),
          onError(601, $error)
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
    public function replay_count_complete()
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
            $ys = $xs->replay(null, 3, null, $this->scheduler);
        });

        $this->scheduler->scheduleAbsolute(450, function () use (&$ys, $xs, $results, &$subscription) {
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
          onNext(451, 5),
          onNext(452, 6),
          onNext(453, 7),
          onNext(521, 11),
          onNext(561, 20),
          onCompleted(601)
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
    public function replay_count_dispose()
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
            $ys = $xs->replay(null, 3, null, $this->scheduler);
        });

        $this->scheduler->scheduleAbsolute(450, function () use (&$ys, $xs, $results, &$subscription) {
            $subscription = $ys->subscribe($results);
        });

        $this->scheduler->scheduleAbsolute(475, function () use (&$subscription) {
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

        $this->assertMessages([
          onNext(451, 5),
          onNext(452, 6),
          onNext(453, 7)
        ],
          $results->getMessages());


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
    public function replay_count_multiple_connections()
    {
        $xs = new NeverObservable();
        $ys = $xs->replay(null, 3);

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
    public function replay_count_zip_complete()
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
            return $xs->replay(function (Observable $ys) {
                return $ys->take(6)->repeat();
            }, 3, null, $this->scheduler);
        }, 610);

        $this->assertMessages([
          onNext(221, 3),
          onNext(281, 4),
          onNext(291, 1),
          onNext(341, 8),
          onNext(361, 5),
          onNext(371, 6),
          onNext(372, 8),
          onNext(373, 5),
          onNext(374, 6),
          onNext(391, 7),
          onNext(411, 13),
          onNext(431, 2),
          onNext(432, 7),
          onNext(433, 13),
          onNext(434, 2),
          onNext(451, 9),
          onNext(521, 11),
          onNext(561, 20),
          onNext(562, 9),
          onNext(563, 11),
          onNext(564, 20),
          onNext(602, 9),
          onNext(603, 11),
          onNext(604, 20),
          onNext(606, 9),
          onNext(607, 11),
          onNext(608, 20)
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
    public function replay_count_zip_error()
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
            return $xs->replay(function (Observable $ys) {
                return $ys->take(6)->repeat();
            }, 3, null, $this->scheduler);
        });

        $this->assertMessages([
          onNext(221, 3),
          onNext(281, 4),
          onNext(291, 1),
          onNext(341, 8),
          onNext(361, 5),
          onNext(371, 6),
          onNext(372, 8),
          onNext(373, 5),
          onNext(374, 6),
          onNext(391, 7),
          onNext(411, 13),
          onNext(431, 2),
          onNext(432, 7),
          onNext(433, 13),
          onNext(434, 2),
          onNext(451, 9),
          onNext(521, 11),
          onNext(561, 20),
          onNext(562, 9),
          onNext(563, 11),
          onNext(564, 20),
          onError(601, $error)
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
    public function replay_count_zip_dispose()
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
            return $xs->replay(function (Observable $ys) {
                return $ys->take(6)->repeat();
            }, 3, null, $this->scheduler);
        }, 470);

        $this->assertMessages([
          onNext(221, 3),
          onNext(281, 4),
          onNext(291, 1),
          onNext(341, 8),
          onNext(361, 5),
          onNext(371, 6),
          onNext(372, 8),
          onNext(373, 5),
          onNext(374, 6),
          onNext(391, 7),
          onNext(411, 13),
          onNext(431, 2),
          onNext(432, 7),
          onNext(433, 13),
          onNext(434, 2),
          onNext(451, 9)
        ], $results->getMessages());

        $this->assertSubscriptions([
          subscribe(200, 470)
        ],
          $xs->getSubscriptions()
        );
    }

    /**
     * @test
     */
    public function replay_time_basic()
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
            $ys = $xs->replay(null, null, 150, $this->scheduler);
        });

        $this->scheduler->scheduleAbsolute(450, function () use (&$ys, $xs, $results, &$subscription) {
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

        $this->assertMessages([
          onNext(451, 8),
          onNext(452, 5),
          onNext(453, 6),
          onNext(454, 7),
          onNext(521, 11)
        ],
          $results->getMessages()
        );


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
    public function replay_time_error()
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
            $ys = $xs->replay(null, null, 75, $this->scheduler);
        });

        $this->scheduler->scheduleAbsolute(450, function () use (&$ys, $xs, $results, &$subscription) {
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
          onNext(451, 7),
          onNext(521, 11),
          onNext(561, 20),
          onError(601, $error)
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
    public function replay_time_complete()
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
            $ys = $xs->replay(null, null, 85, $this->scheduler);
        });

        $this->scheduler->scheduleAbsolute(450, function () use (&$ys, $xs, $results, &$subscription) {
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
          onNext(451, 6),
          onNext(452, 7),
          onNext(521, 11),
          onNext(561, 20),
          onCompleted(601)
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
    public function replay_time_dispose()
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
            $ys = $xs->replay(null, null, 100, $this->scheduler);
        });

        $this->scheduler->scheduleAbsolute(450, function () use (&$ys, $xs, $results, &$subscription) {
            $subscription = $ys->subscribe($results);
        });

        $this->scheduler->scheduleAbsolute(475, function () use (&$subscription) {
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

        $this->assertMessages([
          onNext(451, 5),
          onNext(452, 6),
          onNext(453, 7)
        ],
          $results->getMessages());


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
    public function replay_time_multiple_connections()
    {
        $xs = new NeverObservable();
        $ys = $xs->replay(null, null, 100);

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
    public function replay_time_zip_complete()
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
            return $xs->replay(function (Observable $ys) {
                return $ys->take(6)->repeat();
            }, null, 50, $this->scheduler);
        }, 610);

        $this->assertMessages([
          onNext(221, 3),
          onNext(281, 4),
          onNext(291, 1),
          onNext(341, 8),
          onNext(361, 5),
          onNext(371, 6),
          onNext(372, 8),
          onNext(373, 5),
          onNext(374, 6),
          onNext(391, 7),
          onNext(411, 13),
          onNext(431, 2),
          onNext(432, 7),
          onNext(433, 13),
          onNext(434, 2),
          onNext(451, 9),
          onNext(521, 11),
          onNext(561, 20),
          onNext(562, 11),
          onNext(563, 20),
          onNext(602, 20),
          onNext(604, 20),
          onNext(606, 20),
          onNext(608, 20)
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
    public function replay_time_zip_errorr()
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
            return $xs->replay(function (Observable $ys) {
                return $ys->take(6)->repeat();
            }, null, 50, $this->scheduler);
        });

        $this->assertMessages([
          onNext(221, 3),
          onNext(281, 4),
          onNext(291, 1),
          onNext(341, 8),
          onNext(361, 5),
          onNext(371, 6),
          onNext(372, 8),
          onNext(373, 5),
          onNext(374, 6),
          onNext(391, 7),
          onNext(411, 13),
          onNext(431, 2),
          onNext(432, 7),
          onNext(433, 13),
          onNext(434, 2),
          onNext(451, 9),
          onNext(521, 11),
          onNext(561, 20),
          onNext(562, 11),
          onNext(563, 20),
          onError(601, $error)
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
    public function replay_time_zip_dispose()
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
            return $xs->replay(function (Observable $ys) {
                return $ys->take(6)->repeat();
            }, null, 50, $this->scheduler);
        }, 470);

        $this->assertMessages([
          onNext(221, 3),
          onNext(281, 4),
          onNext(291, 1),
          onNext(341, 8),
          onNext(361, 5),
          onNext(371, 6),
          onNext(372, 8),
          onNext(373, 5),
          onNext(374, 6),
          onNext(391, 7),
          onNext(411, 13),
          onNext(431, 2),
          onNext(432, 7),
          onNext(433, 13),
          onNext(434, 2),
          onNext(451, 9)
        ], $results->getMessages());

        $this->assertSubscriptions([
          subscribe(200, 470)
        ],
          $xs->getSubscriptions()
        );
    }

}
