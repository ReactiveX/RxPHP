<?php

declare(strict_types = 1);

namespace Rx\Functional\Subject;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable;
use Rx\Observer\CallbackObserver;
use Rx\Scheduler\ImmediateScheduler;
use Rx\Subject\ReplaySubject;

class ReplaySubjectTest extends FunctionalTestCase
{
    public function testInfinite()
    {
        $xs = $this->createHotObservable([
          onNext(70, 1),
          onNext(110, 2),
          onNext(220, 3),
          onNext(270, 4),
          onNext(340, 5),
          onNext(410, 6),
          onNext(520, 7),
          onNext(630, 8),
          onNext(710, 9),
          onNext(870, 10),
          onNext(940, 11),
          onNext(1020, 12)
        ]);


        $results1 = $this->scheduler->createObserver();
        $results2 = $this->scheduler->createObserver();
        $results3 = $this->scheduler->createObserver();

        $this->scheduler->scheduleAbsoluteWithState(null, 100, function () use (&$subject) {
            $subject = new ReplaySubject(3, 100, $this->scheduler);
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 200, function () use (&$subscription, &$xs, &$subject) {
            $subscription = $xs->subscribe($subject);
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 1000, function () use (&$subscription) {
            $subscription->dispose();
        });

        $this->scheduler->scheduleAbsoluteWithState(null, 300, function () use (&$subscription1, &$subject, &$results1) {
            $subscription1 = $subject->subscribe($results1);
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 400, function () use (&$subscription2, &$subject, &$results2) {
            $subscription2 = $subject->subscribe($results2);
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 900, function () use (&$subscription3, &$subject, &$results3) {
            $subscription3 = $subject->subscribe($results3);
        });

        $this->scheduler->scheduleAbsoluteWithState(null, 600, function () use (&$subscription1) {
            $subscription1->dispose();
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 700, function () use (&$subscription2) {
            $subscription2->dispose();
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 800, function () use (&$subscription1) {
            $subscription1->dispose();
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 950, function () use (&$subscription3) {
            $subscription3->dispose();
        });

        $this->scheduler->start();

        $this->assertMessages(
          [
            onNext(301, 3),
            onNext(302, 4),
            onNext(341, 5),
            onNext(411, 6),
            onNext(521, 7)
          ],
          $results1->getMessages()
        );

        $this->assertMessages(
          [
            onNext(401, 5),
            onNext(411, 6),
            onNext(521, 7),
            onNext(631, 8)
          ],
          $results2->getMessages()
        );

        $this->assertMessages(
          [
            onNext(901, 10),
            onNext(941, 11)
          ],
          $results3->getMessages()
        );
    }

    public function testInfinite2()
    {
        $xs = $this->createHotObservable([
          onNext(70, 1),
          onNext(110, 2),
          onNext(220, 3),
          onNext(270, 4),
          onNext(280, -1),
          onNext(290, -2),
          onNext(340, 5),
          onNext(410, 6),
          onNext(520, 7),
          onNext(630, 8),
          onNext(710, 9),
          onNext(870, 10),
          onNext(940, 11),
          onNext(1020, 12)
        ]);


        $results1 = $this->scheduler->createObserver();
        $results2 = $this->scheduler->createObserver();
        $results3 = $this->scheduler->createObserver();

        $this->scheduler->scheduleAbsoluteWithState(null, 100, function () use (&$subject) {
            $subject = new ReplaySubject(3, 100, $this->scheduler);
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 200, function () use (&$subject, &$subscription, $xs) {
            $subscription = $xs->subscribe($subject);
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 1000, function () use (&$subscription) {
            $subscription->dispose();
        });

        $this->scheduler->scheduleAbsoluteWithState(null, 300, function () use (&$subject, &$subscription1, &$results1) {
            $subscription1 = $subject->subscribe($results1);
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 400, function () use (&$subject, &$subscription2, &$results2) {
            $subscription2 = $subject->subscribe($results2);
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 900, function () use (&$subject, &$subscription3, &$results3) {
            $subscription3 = $subject->subscribe($results3);
        });

        $this->scheduler->scheduleAbsoluteWithState(null, 600, function () use (&$subscription1) {
            $subscription1->dispose();
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 700, function () use (&$subscription2) {
            $subscription2->dispose();
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 800, function () use (&$subscription1) {
            $subscription1->dispose();
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 950, function () use (&$subscription3) {
            $subscription3->dispose();
        });

        $this->scheduler->start();

        $this->assertMessages([

          onNext(301, 4),
          onNext(302, -1),
          onNext(303, -2),
          onNext(341, 5),
          onNext(411, 6),
          onNext(521, 7)
        ],
          $results1->getMessages());

        $this->assertMessages([
          onNext(401, 5),
          onNext(411, 6),
          onNext(521, 7),
          onNext(631, 8)
        ],
          $results2->getMessages()
        );

        $this->assertMessages([
          onNext(901, 10),
          onNext(941, 11)
        ],
          $results3->getMessages());

    }

    public function testFinite()
    {
        $xs = $this->createHotObservable([
          onNext(70, 1),
          onNext(110, 2),
          onNext(220, 3),
          onNext(270, 4),
          onNext(340, 5),
          onNext(410, 6),
          onNext(520, 7),
          onCompleted(630),
          onNext(640, 9),
          onCompleted(650),
          onError(660, new \Exception())
        ]);


        $results1 = $this->scheduler->createObserver();
        $results2 = $this->scheduler->createObserver();
        $results3 = $this->scheduler->createObserver();

        $this->scheduler->scheduleAbsoluteWithState(null, 100, function () use (&$subject) {
            $subject = new ReplaySubject(3, 100, $this->scheduler);
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 200, function () use (&$subject, &$subscription, $xs) {
            $subscription = $xs->subscribe($subject);
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 1000, function () use (&$subscription) {
            $subscription->dispose();
        });

        $this->scheduler->scheduleAbsoluteWithState(null, 300, function () use (&$subject, &$subscription1, &$results1) {
            $subscription1 = $subject->subscribe($results1);
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 400, function () use (&$subject, &$subscription2, &$results2) {
            $subscription2 = $subject->subscribe($results2);
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 900, function () use (&$subject, &$subscription3, &$results3) {
            $subscription3 = $subject->subscribe($results3);
        });

        $this->scheduler->scheduleAbsoluteWithState(null, 600, function () use (&$subscription1) {
            $subscription1->dispose();
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 700, function () use (&$subscription2) {
            $subscription2->dispose();
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 800, function () use (&$subscription1) {
            $subscription1->dispose();
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 950, function () use (&$subscription3) {
            $subscription3->dispose();
        });

        $this->scheduler->start();

        $this->assertMessages([
          onNext(301, 3),
          onNext(302, 4),
          onNext(341, 5),
          onNext(411, 6),
          onNext(521, 7)
        ],
          $results1->getMessages());

        $this->assertMessages([
          onNext(401, 5),
          onNext(411, 6),
          onNext(521, 7),
          onCompleted(631)
        ], $results2->getMessages());

        $this->assertMessages([onCompleted(901)], $results3->getMessages());

    }

    public function testError()
    {
        $error = new \Exception();

        $xs = $this->createHotObservable([
          onNext(70, 1),
          onNext(110, 2),
          onNext(220, 3),
          onNext(270, 4),
          onNext(340, 5),
          onNext(410, 6),
          onNext(520, 7),
          onError(630, $error),
          onNext(640, 9),
          onCompleted(650),
          onError(660, new \Exception())
        ]);

        $results1 = $this->scheduler->createObserver();
        $results2 = $this->scheduler->createObserver();
        $results3 = $this->scheduler->createObserver();

        $this->scheduler->scheduleAbsoluteWithState(null, 100, function () use (&$subject) {
            $subject = new ReplaySubject(3, 100, $this->scheduler);
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 200, function () use (&$subject, &$subscription, $xs) {
            $subscription = $xs->subscribe($subject);
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 1000, function () use (&$subscription) {
            $subscription->dispose();
        });

        $this->scheduler->scheduleAbsoluteWithState(null, 300, function () use (&$subject, &$subscription1, &$results1) {
            $subscription1 = $subject->subscribe($results1);
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 400, function () use (&$subject, &$subscription2, &$results2) {
            $subscription2 = $subject->subscribe($results2);
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 900, function () use (&$subject, &$subscription3, &$results3) {
            $subscription3 = $subject->subscribe($results3);
        });

        $this->scheduler->scheduleAbsoluteWithState(null, 600, function () use (&$subscription1) {
            $subscription1->dispose();
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 700, function () use (&$subscription2) {
            $subscription2->dispose();
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 800, function () use (&$subscription1) {
            $subscription1->dispose();
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 950, function () use (&$subscription3) {
            $subscription3->dispose();
        });

        $this->scheduler->start();

        $this->assertMessages([
          onNext(301, 3),
          onNext(302, 4),
          onNext(341, 5),
          onNext(411, 6),
          onNext(521, 7)
        ], $results1->getMessages());

        $this->assertMessages([
          onNext(401, 5),
          onNext(411, 6),
          onNext(521, 7),
          onError(631, $error)
        ], $results2->getMessages());

        $this->assertMessages([
          onError(901, $error)
        ], $results3->getMessages());
    }

    public function testCanceled()
    {
        $xs = $this->createHotObservable([
          onCompleted(630),
          onNext(640, 9),
          onCompleted(650),
          onError(660, new \Exception())
        ]);

        $results1 = $this->scheduler->createObserver();
        $results2 = $this->scheduler->createObserver();
        $results3 = $this->scheduler->createObserver();

        $this->scheduler->scheduleAbsoluteWithState(null, 100, function () use (&$subject) {
            $subject = new ReplaySubject(3, 100, $this->scheduler);
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 200, function () use (&$subject, &$subscription, $xs) {
            $subscription = $xs->subscribe($subject);
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 1000, function () use (&$subscription) {
            $subscription->dispose();
        });

        $this->scheduler->scheduleAbsoluteWithState(null, 300, function () use (&$subject, &$subscription1, &$results1) {
            $subscription1 = $subject->subscribe($results1);
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 400, function () use (&$subject, &$subscription2, &$results2) {
            $subscription2 = $subject->subscribe($results2);
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 900, function () use (&$subject, &$subscription3, &$results3) {
            $subscription3 = $subject->subscribe($results3);
        });

        $this->scheduler->scheduleAbsoluteWithState(null, 600, function () use (&$subscription1) {
            $subscription1->dispose();
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 700, function () use (&$subscription2) {
            $subscription2->dispose();
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 800, function () use (&$subscription1) {
            $subscription1->dispose();
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 950, function () use (&$subscription3) {
            $subscription3->dispose();
        });

        $this->scheduler->start();

        $this->assertMessages([], $results1->getMessages());

        $this->assertMessages([
          onCompleted(631)
        ], $results2->getMessages());

        $this->assertMessages([
          onCompleted(901)
        ], $results3->getMessages());

    }

    public function testDisposed()
    {
        $results1 = $this->scheduler->createObserver();
        $results2 = $this->scheduler->createObserver();
        $results3 = $this->scheduler->createObserver();

        $this->scheduler->scheduleAbsoluteWithState(null, 100, function () use (&$subject) {
            $subject = new ReplaySubject(null, null, $this->scheduler);
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 200, function () use (&$subject, &$subscription1, &$results1) {
            $subscription1 = $subject->subscribe($results1);
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 300, function () use (&$subject, &$subscription2, &$results2) {
            $subscription2 = $subject->subscribe($results2);
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 400, function () use (&$subject, &$subscription3, &$results3) {
            $subscription3 = $subject->subscribe($results3);
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 500, function () use (&$subject, &$subscription1) {
            $subscription1->dispose();
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 600, function () use (&$subject) {
            $subject->dispose();
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 700, function () use (&$subscription2) {
            $subscription2->dispose();
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 800, function () use (&$subscription3) {
            $subscription3->dispose();
        });

        $this->scheduler->scheduleAbsoluteWithState(null, 150, function () use (&$subject) {
            $subject->onNext(1);
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 250, function () use (&$subject) {
            $subject->onNext(2);
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 350, function () use (&$subject) {
            $subject->onNext(3);
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 450, function () use (&$subject) {
            $subject->onNext(4);
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 550, function () use (&$subject) {
            $subject->onNext(5);
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 650, function () use (&$subject) {
            $this->assertException(function () use (&$subject) {
                $subject->onNext(6);
            });
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 750, function () use (&$subject) {
            $this->assertException(function () use (&$subject) {
                $subject->onCompleted();
            });
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 850, function () use (&$subject) {
            $this->assertException(function () use (&$subject) {
                $subject->onError(new \Exception());
            });
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 950, function () use (&$subject) {
            $this->assertException(function () use (&$subject) {
                $subject->subscribe(new CallbackObserver());
            });
        });

        $this->scheduler->start();

        $this->assertMessages([
          onNext(201, 1),
          onNext(251, 2),
          onNext(351, 3),
          onNext(451, 4)
        ], $results1->getMessages());

        $this->assertMessages([
          onNext(301, 1),
          onNext(302, 2),
          onNext(351, 3),
          onNext(451, 4),
          onNext(551, 5)
        ], $results2->getMessages());

        $this->assertMessages([
          onNext(401, 1),
          onNext(402, 2),
          onNext(403, 3),
          onNext(451, 4),
          onNext(551, 5)
        ], $results3->getMessages());

    }

    public function testDiesOut()
    {
        $xs = $this->createHotObservable([
          onNext(70, 1),
          onNext(110, 2),
          onNext(220, 3),
          onNext(270, 4),
          onNext(340, 5),
          onNext(410, 6),
          onNext(520, 7),
          onCompleted(580)
        ]);


        $results1 = $this->scheduler->createObserver();
        $results2 = $this->scheduler->createObserver();
        $results3 = $this->scheduler->createObserver();
        $results4 = $this->scheduler->createObserver();

        $this->scheduler->scheduleAbsoluteWithState(null, 100, function () use (&$subject) {
            $subject = new ReplaySubject(9007199254740991, 100, $this->scheduler);
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 200, function () use ($xs, &$subject) {
            $xs->subscribe($subject);
        });

        $this->scheduler->scheduleAbsoluteWithState(null, 300, function () use (&$subject, &$results1) {
            $subject->subscribe($results1);
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 400, function () use (&$subject, &$results2) {
            $subject->subscribe($results2);
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 600, function () use (&$subject, &$results3) {
            $subject->subscribe($results3);
        });
        $this->scheduler->scheduleAbsoluteWithState(null, 900, function () use (&$subject, &$results4) {
            $subject->subscribe($results4);
        });

        $this->scheduler->start();

        $this->assertMessages([
          onNext(301, 3),
          onNext(302, 4),
          onNext(341, 5),
          onNext(411, 6),
          onNext(521, 7),
          onCompleted(581)
        ], $results1->getMessages());


        $this->assertMessages([
          onNext(401, 5),
          onNext(411, 6),
          onNext(521, 7),
          onCompleted(581)
        ], $results2->getMessages());

        $this->assertMessages([
          onNext(601, 7),
          onCompleted(602)
        ], $results3->getMessages());

        $this->assertMessages([
          onCompleted(901)
        ], $results4->getMessages());

    }

    /**
     * @test
     */
    public function it_replays_with_immediate_scheduler() {
        $rs = new ReplaySubject();

        $o = Observable::fromArray(range(1,5));

        $o->subscribe($rs);

        $result = [];
        $completed = false;

        $rs->subscribe(function ($x) use (&$result) {
                $result[] = $x;
            },
            function ($e) {
                $this->fail('Should not have failed');
            },
            function () use (&$result, &$completed) {
                $completed = true;
                $this->assertEquals($result, range(1,5));
            }
        );

        $this->assertTrue($completed);
    }
}
