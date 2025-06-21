<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable;
use Rx\Observable\NeverObservable;

class PublishValueTest extends FunctionalTestCase
{

    public function add($x, $y)
    {
        return $x + $y;
    }

    /**
     * @test
     */
    public function publishLast_basic(): void
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

        $this->scheduler->scheduleAbsolute(100, function () use (&$ys, $xs): void {
            $ys = $xs->publishValue(1979);
        });

        $this->scheduler->scheduleAbsolute(200, function () use (&$ys, $xs, $results, &$subscription): void {
            $subscription = $ys->subscribe($results);
        });

        $this->scheduler->scheduleAbsolute(1000, function () use (&$subscription): void {
            $subscription->dispose();
        });

        $this->scheduler->scheduleAbsolute(300, function () use (&$connection, &$ys): void {
            $connection = $ys->connect();
        });

        $this->scheduler->scheduleAbsolute(400, function () use (&$connection): void {
            $connection->dispose();
        });

        $this->scheduler->scheduleAbsolute(500, function () use (&$connection, &$ys): void {
            $connection = $ys->connect();
        });

        $this->scheduler->scheduleAbsolute(550, function () use (&$connection): void {
            $connection->dispose();
        });

        $this->scheduler->scheduleAbsolute(650, function () use (&$connection, &$ys): void {
            $connection = $ys->connect();
        });

        $this->scheduler->scheduleAbsolute(800, function () use (&$connection): void {
            $connection->dispose();
        });


        $this->scheduler->start();

        $this->assertMessages([
          onNext(200, 1979),
          onNext(340, 8),
          onNext(360, 5),
          onNext(370, 6),
          onNext(390, 7),
          onNext(520, 11)
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
    public function publishValue_error(): void
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

        $this->scheduler->scheduleAbsolute(100, function () use (&$ys, $xs): void {
            $ys = $xs->publishValue(1979);
        });

        $this->scheduler->scheduleAbsolute(200, function () use (&$ys, $xs, $results, &$subscription): void {
            $subscription = $ys->subscribe($results);
        });

        $this->scheduler->scheduleAbsolute(1000, function () use (&$subscription): void {
            $subscription->dispose();
        });

        $this->scheduler->scheduleAbsolute(300, function () use (&$connection, &$ys): void {
            $connection = $ys->connect();
        });

        $this->scheduler->scheduleAbsolute(400, function () use (&$connection): void {
            $connection->dispose();
        });

        $this->scheduler->scheduleAbsolute(500, function () use (&$connection, &$ys): void {
            $connection = $ys->connect();
        });

        $this->scheduler->scheduleAbsolute(800, function () use (&$connection): void {
            $connection->dispose();
        });


        $this->scheduler->start();

        $this->assertMessages([
          onNext(200, 1979),
          onNext(340, 8),
          onNext(360, 5),
          onNext(370, 6),
          onNext(390, 7),
          onNext(520, 11),
          onNext(560, 20),
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
    public function publishValue_complete(): void
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

        $this->scheduler->scheduleAbsolute(100, function () use (&$ys, $xs): void {
            $ys = $xs->publishValue(1979);
        });

        $this->scheduler->scheduleAbsolute(200, function () use (&$ys, $xs, $results, &$subscription): void {
            $subscription = $ys->subscribe($results);
        });

        $this->scheduler->scheduleAbsolute(1000, function () use (&$subscription): void {
            $subscription->dispose();
        });

        $this->scheduler->scheduleAbsolute(300, function () use (&$connection, &$ys): void {
            $connection = $ys->connect();
        });

        $this->scheduler->scheduleAbsolute(400, function () use (&$connection): void {
            $connection->dispose();
        });

        $this->scheduler->scheduleAbsolute(500, function () use (&$connection, &$ys): void {
            $connection = $ys->connect();
        });

        $this->scheduler->scheduleAbsolute(800, function () use (&$connection): void {
            $connection->dispose();
        });


        $this->scheduler->start();

        $this->assertMessages([
          onNext(200, 1979),
          onNext(340, 8),
          onNext(360, 5),
          onNext(370, 6),
          onNext(390, 7),
          onNext(520, 11),
          onNext(560, 20),
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
    public function publishValue_dispose(): void
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

        $this->scheduler->scheduleAbsolute(100, function () use (&$ys, $xs): void {
            $ys = $xs->publishValue(1979);
        });

        $this->scheduler->scheduleAbsolute(200, function () use (&$ys, $xs, $results, &$subscription): void {
            $subscription = $ys->subscribe($results);
        });

        $this->scheduler->scheduleAbsolute(350, function () use (&$subscription): void {
            $subscription->dispose();
        });

        $this->scheduler->scheduleAbsolute(300, function () use (&$connection, &$ys): void {
            $connection = $ys->connect();
        });

        $this->scheduler->scheduleAbsolute(400, function () use (&$connection): void {
            $connection->dispose();
        });

        $this->scheduler->scheduleAbsolute(500, function () use (&$connection, &$ys): void {
            $connection = $ys->connect();
        });

        $this->scheduler->scheduleAbsolute(550, function () use (&$connection): void {
            $connection->dispose();
        });

        $this->scheduler->scheduleAbsolute(650, function () use (&$connection, &$ys): void {
            $connection = $ys->connect();
        });

        $this->scheduler->scheduleAbsolute(800, function () use (&$connection): void {
            $connection->dispose();
        });


        $this->scheduler->start();

        $this->assertMessages([
          onNext(200, 1979),
          onNext(340, 8)
        ], $results->getMessages());


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
    public function publishValue_multiple_connections(): void
    {
        $xs = new NeverObservable();
        $ys = $xs->publishValue(1979);

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
    public function publishValue_zip_complete(): void
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
            return $xs->publishValue(1979, function (Observable $ys) {
                return $ys->zip([$ys->skip(1)], [$this, 'add']);
            });
        });

        $this->assertMessages([
          onNext(220, 1982),
          onNext(280, 7),
          onNext(290, 5),
          onNext(340, 9),
          onNext(360, 13),
          onNext(370, 11),
          onNext(390, 13),
          onNext(410, 20),
          onNext(430, 15),
          onNext(450, 11),
          onNext(520, 20),
          onNext(560, 31),
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
    public function publishValue_zip_error(): void
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
            return $xs->publishValue(1979, function (Observable $ys) {
                return $ys->zip([$ys->skip(1)], [$this, 'add']);
            });
        });

        $this->assertMessages([
            onNext(220, 1982),
            onNext(280, 7),
            onNext(290, 5),
            onNext(340, 9),
            onNext(360, 13),
            onNext(370, 11),
            onNext(390, 13),
            onNext(410, 20),
            onNext(430, 15),
            onNext(450, 11),
            onNext(520, 20),
            onNext(560, 31),
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
    public function publishValue_zip_dispose(): void
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
            return $xs->publishValue(1979, function (Observable $ys) {
                return $ys->zip([$ys->skip(1)], [$this, 'add']);
            });
        }, 470);

        $this->assertMessages([
          onNext(220, 1982),
          onNext(280, 7),
          onNext(290, 5),
          onNext(340, 9),
          onNext(360, 13),
          onNext(370, 11),
          onNext(390, 13),
          onNext(410, 20),
          onNext(430, 15),
          onNext(450, 11)
        ],
          $results->getMessages()
        );

        $this->assertSubscriptions([
          subscribe(200, 470)
        ],
          $xs->getSubscriptions()
        );
    }
}
