<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Rx\Disposable\CallbackDisposable;
use Rx\Functional\FunctionalTestCase;
use Rx\Observable\AnonymousObservable;
use Rx\Observable;
use Rx\Observable\ConnectableObservable;
use Rx\Observable\MulticastObservable;
use Rx\Observer\CallbackObserver;
use Rx\Subject\Subject;
use Rx\Testing\TestSubject;

class MulticastTest extends FunctionalTestCase
{

    public function add($x, $y)
    {
        return $x + $y;
    }

    /**
     * @test
     */
    public function multicast_hot_1()
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

        $ys       = null;
        $observer = $this->scheduler->createObserver();
        $d1       = null;
        $d2       = null;
        $subject  = new Subject();

        $this->scheduler->scheduleAbsolute(50, function () use (&$ys, $xs, $subject) {
            $ys = $xs->multicast($subject);
        });

        $this->scheduler->scheduleAbsolute(100, function () use (&$d1, &$ys, $observer) {
            $d1 = $ys->subscribe($observer);
        });

        $this->scheduler->scheduleAbsolute(200, function () use (&$d2, &$ys) {
            $d2 = $ys->connect();
        });

        $this->scheduler->scheduleAbsolute(300, function () use (&$d1) {
            $d1->dispose();
        });


        $this->scheduler->start();

        $this->assertMessages([
          onNext(210, 3),
          onNext(240, 4),
          onNext(270, 5)
        ],
          $observer->getMessages()
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
    public function multicast_hot_2()
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

        $ys       = null;
        $observer = $this->scheduler->createObserver();
        $d1       = null;
        $d2       = null;
        $subject  = new Subject();

        $this->scheduler->scheduleAbsolute(50, function () use (&$ys, $xs, $subject) {
            $ys = $xs->multicast($subject);
        });


        $this->scheduler->scheduleAbsolute(100, function () use (&$d2, &$ys) {
            $d2 = $ys->connect();
        });

        $this->scheduler->scheduleAbsolute(200, function () use (&$d1, &$ys, $observer) {
            $d1 = $ys->subscribe($observer);
        });

        $this->scheduler->scheduleAbsolute(300, function () use (&$d1) {
            $d1->dispose();
        });


        $this->scheduler->start();

        $this->assertMessages([
          onNext(210, 3),
          onNext(240, 4),
          onNext(270, 5)
        ],
          $observer->getMessages()
        );

        $this->assertSubscriptions([
          subscribe(100, 390)
        ],
          $xs->getSubscriptions()
        );
    }


    /**
     * @test
     */
    public function multicast_hot_3()
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

        $ys       = null;
        $observer = $this->scheduler->createObserver();
        $d1       = null;
        $d2       = null;
        $subject  = new Subject();

        $this->scheduler->scheduleAbsolute(50, function () use (&$ys, $xs, $subject) {
            $ys = $xs->multicast($subject);
        });

        $this->scheduler->scheduleAbsolute(100, function () use (&$d2, &$ys) {
            $d2 = $ys->connect();
        });

        $this->scheduler->scheduleAbsolute(200, function () use (&$d1, &$ys, $observer) {
            $d1 = $ys->subscribe($observer);
        });

        $this->scheduler->scheduleAbsolute(300, function () use (&$d2) {
            $d2->dispose();
        });

        $this->scheduler->scheduleAbsolute(335, function () use (&$d2, &$ys) {
            $d2 = $ys->connect();
        });

        $this->scheduler->start();

        $this->assertMessages([
          onNext(210, 3),
          onNext(240, 4),
          onNext(270, 5),
          onNext(340, 7),
          onCompleted(390)
        ],
          $observer->getMessages()
        );

        $this->assertSubscriptions([
          subscribe(100, 300),
          subscribe(335, 390)
        ],
          $xs->getSubscriptions()
        );
    }

    /**
     * @test
     */
    public function multicast_hot_error_1()
    {
        $error = new \Exception();

        $xs = $this->createHotObservable([
          onNext(40, 0),
          onNext(90, 1),
          onNext(150, 2),
          onNext(210, 3),
          onNext(240, 4),
          onNext(270, 5),
          onNext(330, 6),
          onNext(340, 7),
          onError(390, $error)
        ]);

        $ys       = null;
        $observer = $this->scheduler->createObserver();
        $d1       = null;
        $d2       = null;
        $subject  = new Subject();

        $this->scheduler->scheduleAbsolute(50, function () use (&$ys, $xs, $subject) {
            $ys = $xs->multicast($subject);
        });

        $this->scheduler->scheduleAbsolute(100, function () use (&$d2, &$ys) {
            $d2 = $ys->connect();
        });

        $this->scheduler->scheduleAbsolute(200, function () use (&$d1, &$ys, $observer) {
            $d1 = $ys->subscribe($observer);
        });

        $this->scheduler->scheduleAbsolute(300, function () use (&$d2) {
            $d2->dispose();
        });

        $this->scheduler->scheduleAbsolute(335, function () use (&$d2, &$ys) {
            $d2 = $ys->connect();
        });


        $this->scheduler->start();

        $this->assertMessages([
          onNext(210, 3),
          onNext(240, 4),
          onNext(270, 5),
          onNext(340, 7),
          onError(390, $error)
        ],
          $observer->getMessages()
        );

        $this->assertSubscriptions([
          subscribe(100, 300),
          subscribe(335, 390)
        ],
          $xs->getSubscriptions()
        );
    }


    /**
     * @test
     */
    public function multicast_hot_error_2()
    {
        $error = new \Exception();

        $xs = $this->createHotObservable([
          onNext(40, 0),
          onNext(90, 1),
          onNext(150, 2),
          onNext(210, 3),
          onNext(240, 4),
          onNext(270, 5),
          onNext(330, 6),
          onNext(340, 7),
          onError(390, $error)
        ]);

        $ys       = null;
        $observer = $this->scheduler->createObserver();
        $d1       = null;
        $d2       = null;
        $subject  = new Subject();

        $this->scheduler->scheduleAbsolute(50, function () use (&$ys, $xs, $subject) {
            $ys = $xs->multicast($subject);
        });

        $this->scheduler->scheduleAbsolute(100, function () use (&$d2, &$ys) {
            $d2 = $ys->connect();
        });

        $this->scheduler->scheduleAbsolute(400, function () use (&$d1, &$ys, $observer) {
            $d1 = $ys->subscribe($observer);
        });


        $this->scheduler->start();

        $this->assertMessages([
          onError(400, $error)
        ],
          $observer->getMessages()
        );

        $this->assertSubscriptions([
          subscribe(100, 390)
        ],
          $xs->getSubscriptions()
        );
    }

    /**
     * @test
     */
    public function multicast_hot_completed()
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

        $ys       = null;
        $observer = $this->scheduler->createObserver();
        $d1       = null;
        $d2       = null;
        $subject  = new Subject();

        $this->scheduler->scheduleAbsolute(50, function () use (&$ys, $xs, $subject) {
            $ys = $xs->multicast($subject);
        });

        $this->scheduler->scheduleAbsolute(100, function () use (&$d2, &$ys) {
            $d2 = $ys->connect();
        });

        $this->scheduler->scheduleAbsolute(400, function () use (&$d1, &$ys, $observer) {
            $d1 = $ys->subscribe($observer);
        });

        $this->scheduler->start();

        $this->assertMessages([
          onCompleted(400)
        ],
          $observer->getMessages()
        );

        $this->assertSubscriptions([
          subscribe(100, 390)
        ],
          $xs->getSubscriptions()
        );
    }

    /**
     * @test
     */
    public function multicast_cold_completed()
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

            return $xs->multicastWithSelector(
              function () {
                  return new Subject();
              },
              function ($ys) {
                  return $ys;
              });
        });

        $this->assertMessages([
          onNext(210, 3),
          onNext(240, 4),
          onNext(270, 5),
          onNext(330, 6),
          onNext(340, 7),
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
    public function multicast_cold_error()
    {
        $error = new \Exception();

        $xs = $this->createHotObservable([
          onNext(40, 0),
          onNext(90, 1),
          onNext(150, 2),
          onNext(210, 3),
          onNext(240, 4),
          onNext(270, 5),
          onNext(330, 6),
          onNext(340, 7),
          onError(390, $error)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->multicastWithSelector(
              function () {
                  return new Subject();
              },
              function ($ys) {
                  return $ys;
              });
        });

        $this->assertMessages([
          onNext(210, 3),
          onNext(240, 4),
          onNext(270, 5),
          onNext(330, 6),
          onNext(340, 7),
          onError(390, $error)
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
    public function multicast_cold_dispose()
    {

        $xs = $this->createHotObservable([
          onNext(40, 0),
          onNext(90, 1),
          onNext(150, 2),
          onNext(210, 3),
          onNext(240, 4),
          onNext(270, 5),
          onNext(330, 6),
          onNext(340, 7)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->multicastWithSelector(
              function () {
                  return new Subject();
              },
              function ($ys) {
                  return $ys;
              });
        });

        $this->assertMessages([
          onNext(210, 3),
          onNext(240, 4),
          onNext(270, 5),
          onNext(330, 6),
          onNext(340, 7)
        ],
          $results->getMessages()
        );

        $this->assertSubscriptions([
          subscribe(200, 1000)
        ],
          $xs->getSubscriptions()
        );
    }

    /**
     * @test
     */
    public function multicast_cold_zip()
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
            return $xs->multicastWithSelector(
              function () {
                  return new Subject();
              },
              function ($ys) {
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
}
