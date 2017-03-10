<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Rx\Disposable\CallbackDisposable;
use Rx\Functional\FunctionalTestCase;
use Rx\Observable\AnonymousObservable;
use Rx\Observable;
use Rx\Observable\ConnectableObservable;
use Rx\Observer\CallbackObserver;
use Rx\Testing\TestSubject;

class PublishTest extends FunctionalTestCase
{

    public function add($x, $y)
    {
        return $x + $y;
    }

    /**
     * @test
     */
    public function publish_cold_zip()
    {

        $xs = $this->createHotObservable([
            onNext(40, 0),
            onNext(90, 1),
            onNext(150, 2),
            onNext(210, 3),
            onNext(240, 4),
            onNext(270, 5),
            onNext(330, 6),
            onNext(340, 7),
            onCompleted(390)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->publish(function (Observable $ys) {
                return $ys->zip([$ys], [$this, 'add']);
            });
        });

        $this->assertMessages([
            onNext(210, 6),
            onNext(240, 8),
            onNext(270, 10),
            onNext(330, 12),
            onNext(340, 14),
            onCompleted(390)
        ],
          $results->getMessages()
        );

        $this->assertSubscriptions([
          subscribe(200, 390)
        ],
          $xs->getSubscriptions()
        );
    }


    /**
     * @test
     */
    public function refCount_connects_on_first()
    {

        $xs = $this->createHotObservable([
          onNext(210, 1),
          onNext(220, 2),
          onNext(230, 3),
          onNext(240, 4),
          onCompleted(250)
        ]);

        $subject = new TestSubject();
        $conn    = new ConnectableObservable($xs, $subject);

        $results = $this->scheduler->startWithCreate(function () use ($conn) {
            return $conn->refCount();
        });

        $this->assertMessages([
          onNext(210, 1),
          onNext(220, 2),
          onNext(230, 3),
          onNext(240, 4),
          onCompleted(250)
        ],
          $results->getMessages()
        );

        $this->assertTrue($subject->isDisposed());
    }

    /**
     * @test
     */
    public function refCount_not_connected()
    {

        $disconnected = false;

        $count = 0;

        $xs = Observable::defer(function () use (&$count, &$disconnected) {
            $count++;

            return new AnonymousObservable(function () use (&$disconnected) {
                return new CallbackDisposable(function () use (&$disconnected) {
                    $disconnected = true;
                });
            });

        });

        $subject = new TestSubject();
        $conn    = new ConnectableObservable($xs, $subject);
        $refd    = $conn->refCount();
        $dis1    = $refd->subscribe(new CallbackObserver());

        $this->assertEquals(1, $count);
        $this->assertEquals(1, $subject->getSubscribeCount());
        $this->assertFalse($disconnected);


        $dis2 = $refd->subscribe(new CallbackObserver());

        $this->assertEquals(1, $count);
        $this->assertEquals(2, $subject->getSubscribeCount());
        $this->assertFalse($disconnected);

        $dis1->dispose();
        $this->assertFalse($disconnected);

        $dis2->dispose();
        $this->assertTrue($disconnected);

        $disconnected = false;

        $dis3 = $refd->subscribe(new CallbackObserver());
        $this->assertEquals(2, $count);
        $this->assertEquals(3, $subject->getSubscribeCount());
        $this->assertFalse($disconnected);

        $dis3->dispose();
        $this->assertTrue($disconnected);

    }


    /**
     * @test
     */
    public function refCount_publish_basic()
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
            $ys = $xs->publish();
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

        $this->assertMessages([
          onNext(340, 8),
          onNext(360, 5),
          onNext(370, 6),
          onNext(390, 7),
          onNext(520, 11)
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
    public function publish_error()
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
            $ys = $xs->publish();
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
    public function publish_complete()
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
            $ys = $xs->publish();
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
    public function publish_dispose()
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
            $ys = $xs->publish();
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

        $this->assertMessages([
          onNext(340, 8)
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
    public function publish_lambda_zip_complete()
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
            return $xs->publish(function (Observable $ys) {
                return $ys->zip([$ys->skip(1)], [$this, 'add']);
            });
        });

        $this->assertMessages([
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
    public function publish_lambda_zip_error()
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
            return $xs->publish(function (Observable $ys) {
                return $ys->zip([$ys->skip(1)], [$this, 'add']);
            });
        });

        $this->assertMessages([
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
    public function publish_lambda_zip_dispose()
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
            return $xs->publish(function (Observable $ys) {
                return $ys->zip([$ys->skip(1)], [$this, 'add']);
            });
        }, 470);


        $this->assertMessages([
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
