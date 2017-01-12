<?php

declare(strict_types = 1);

namespace Rx\Functional\Subject;


use Rx\Functional\FunctionalTestCase;
use Rx\Observer\CallbackObserver;
use Rx\Subject\AsyncSubject;


class AsyncSubjectTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function infinite()
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

        $subject       = null;
        $subscription  = null;
        $subscription1 = null;
        $subscription2 = null;
        $subscription3 = null;

        $this->scheduler->scheduleAbsolute(100, function () use (&$subject) {
            $subject = new AsyncSubject();
        });

        $this->scheduler->scheduleAbsolute(200, function () use ($xs, &$subscription, &$subject) {
            $subscription = $xs->subscribe($subject);
        });

        $this->scheduler->scheduleAbsolute(1000, function () use (&$subscription) {
            $subscription->dispose();
        });

        $this->scheduler->scheduleAbsolute(300, function () use (&$results1, &$subscription1, &$subject) {
            $subscription1 = $subject->subscribe($results1);
        });

        $this->scheduler->scheduleAbsolute(400, function () use (&$results2, &$subscription2, &$subject) {
            $subscription2 = $subject->subscribe($results2);
        });

        $this->scheduler->scheduleAbsolute(900, function () use (&$results3, &$subscription3, &$subject) {
            $subscription3 = $subject->subscribe($results3);
        });

        $this->scheduler->scheduleAbsolute(600, function () use (&$subscription1) {
            $subscription1->dispose();
        });

        $this->scheduler->scheduleAbsolute(700, function () use (&$subscription2) {
            $subscription2->dispose();
        });

        $this->scheduler->scheduleAbsolute(800, function () use (&$subscription1) {
            $subscription1->dispose();
        });

        $this->scheduler->scheduleAbsolute(950, function () use (&$subscription3) {
            $subscription3->dispose();
        });

        $this->scheduler->start();

        $this->assertMessages([], $results1->getMessages());
        $this->assertMessages([], $results2->getMessages());
        $this->assertMessages([], $results3->getMessages());
    }

    /**
     * @test
     */
    public function finite()
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

        $subject       = null;
        $subscription  = null;
        $subscription1 = null;
        $subscription2 = null;
        $subscription3 = null;

        $this->scheduler->scheduleAbsolute(100, function () use (&$subject) {
            $subject = new AsyncSubject();
        });

        $this->scheduler->scheduleAbsolute(200, function () use ($xs, &$subscription, &$subject) {
            $subscription = $xs->subscribe($subject);
        });

        $this->scheduler->scheduleAbsolute(1000, function () use (&$subscription) {
            $subscription->dispose();
        });

        $this->scheduler->scheduleAbsolute(300, function () use (&$results1, &$subscription1, &$subject) {
            $subscription1 = $subject->subscribe($results1);
        });

        $this->scheduler->scheduleAbsolute(400, function () use (&$results2, &$subscription2, &$subject) {
            $subscription2 = $subject->subscribe($results2);
        });

        $this->scheduler->scheduleAbsolute(900, function () use (&$results3, &$subscription3, &$subject) {
            $subscription3 = $subject->subscribe($results3);
        });

        $this->scheduler->scheduleAbsolute(600, function () use (&$subscription1) {
            $subscription1->dispose();
        });

        $this->scheduler->scheduleAbsolute(700, function () use (&$subscription2) {
            $subscription2->dispose();
        });

        $this->scheduler->scheduleAbsolute(800, function () use (&$subscription1) {
            $subscription1->dispose();
        });

        $this->scheduler->scheduleAbsolute(950, function () use (&$subscription3) {
            $subscription3->dispose();
        });

        $this->scheduler->start();

        $this->assertMessages([], $results1->getMessages());
        $this->assertMessages([onNext(630, 7), onCompleted(630)], $results2->getMessages());
        $this->assertMessages([onNext(900, 7), onCompleted(900)], $results3->getMessages());

    }

    /**
     * @test
     */
    public function error()
    {
        $xs = $this->createHotObservable([
          onNext(70, 1),
          onNext(110, 2),
          onNext(220, 3),
          onNext(270, 4),
          onNext(340, 5),
          onNext(410, 6),
          onNext(520, 7),
          onError(630, new \Exception('some error')),
          onNext(640, 9),
          onCompleted(650),
          onError(660, new \Exception())
        ]);

        $results1 = $this->scheduler->createObserver();
        $results2 = $this->scheduler->createObserver();
        $results3 = $this->scheduler->createObserver();

        $subject       = null;
        $subscription  = null;
        $subscription1 = null;
        $subscription2 = null;
        $subscription3 = null;

        $this->scheduler->scheduleAbsolute(100, function () use (&$subject) {
            $subject = new AsyncSubject();
        });

        $this->scheduler->scheduleAbsolute(200, function () use ($xs, &$subscription, &$subject) {
            $subscription = $xs->subscribe($subject);
        });

        $this->scheduler->scheduleAbsolute(1000, function () use (&$subscription) {
            $subscription->dispose();
        });

        $this->scheduler->scheduleAbsolute(300, function () use (&$results1, &$subscription1, &$subject) {
            $subscription1 = $subject->subscribe($results1);
        });

        $this->scheduler->scheduleAbsolute(400, function () use (&$results2, &$subscription2, &$subject) {
            $subscription2 = $subject->subscribe($results2);
        });

        $this->scheduler->scheduleAbsolute(900, function () use (&$results3, &$subscription3, &$subject) {
            $subscription3 = $subject->subscribe($results3);
        });

        $this->scheduler->scheduleAbsolute(600, function () use (&$subscription1) {
            $subscription1->dispose();
        });

        $this->scheduler->scheduleAbsolute(700, function () use (&$subscription2) {
            $subscription2->dispose();
        });

        $this->scheduler->scheduleAbsolute(800, function () use (&$subscription1) {
            $subscription1->dispose();
        });

        $this->scheduler->scheduleAbsolute(950, function () use (&$subscription3) {
            $subscription3->dispose();
        });

        $this->scheduler->start();

        $this->assertMessages([], $results1->getMessages());
        $this->assertMessages([onError(630, new \Exception('some error'))], $results2->getMessages());
        $this->assertMessages([onError(900, new \Exception('some error'))], $results3->getMessages());

    }


    /**
     * @test
     */
    public function dispose()
    {


        $results1 = $this->scheduler->createObserver();
        $results2 = $this->scheduler->createObserver();
        $results3 = $this->scheduler->createObserver();

        $subject       = null;
        $subscription1 = null;
        $subscription2 = null;
        $subscription3 = null;

        $this->scheduler->scheduleAbsolute(100, function () use (&$subject) {
            $subject = new AsyncSubject();
        });

        $this->scheduler->scheduleAbsolute(200, function () use (&$subscription1, &$subject, $results1) {
            $subscription1 = $subject->subscribe($results1);
        });


        $this->scheduler->scheduleAbsolute(300, function () use (&$subscription2, &$subject, $results2) {
            $subscription2 = $subject->subscribe($results2);
        });

        $this->scheduler->scheduleAbsolute(400, function () use (&$subscription3, &$subject, $results3) {
            $subscription3 = $subject->subscribe($results3);
        });


        $this->scheduler->scheduleAbsolute(500, function () use (&$subscription1) {
            $subscription1->dispose();
        });


        $this->scheduler->scheduleAbsolute(600, function () use (&$subject) {
            $subject->dispose();
        });


        $this->scheduler->scheduleAbsolute(700, function () use (&$subscription2) {
            $subscription2->dispose();
        });


        $this->scheduler->scheduleAbsolute(800, function () use (&$subscription3) {
            $subscription3->dispose();
        });


        $this->scheduler->scheduleAbsolute(150, function () use (&$subject) {
            $subject->onNext(1);
        });

        $this->scheduler->scheduleAbsolute(250, function () use (&$subject) {
            $subject->onNext(2);
        });

        $this->scheduler->scheduleAbsolute(350, function () use (&$subject) {
            $subject->onNext(3);
        });

        $this->scheduler->scheduleAbsolute(450, function () use (&$subject) {
            $subject->onNext(4);
        });

        $this->scheduler->scheduleAbsolute(550, function () use (&$subject) {
            $subject->onNext(5);
        });

        $this->scheduler->scheduleAbsolute(650, function () use (&$subject) {

            $this->assertException(function () use (&$subject) {
                $subject->onNext(6);
            });

        });

        $this->scheduler->scheduleAbsolute(750, function () use (&$subject) {
            $this->assertException(function () use (&$subject) {
                $subject->onCompleted();
            });
        });

        $this->scheduler->scheduleAbsolute(850, function () use (&$subject) {
            $this->assertException(function () use (&$subject) {
                $subject->onError(new \Exception());
            });

        });

        $this->scheduler->scheduleAbsolute(950, function () use (&$subject) {

            $this->assertException(function () use (&$subject) {
                $subject->subscribe(new CallbackObserver());
            });
        });


        $this->scheduler->start();

        $this->assertMessages([], $results1->getMessages());
        $this->assertMessages([], $results2->getMessages());
        $this->assertMessages([], $results3->getMessages());

    }
}
