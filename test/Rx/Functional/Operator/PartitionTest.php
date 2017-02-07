<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable;
use Rx\Testing\TestScheduler;

class PartitionTest extends FunctionalTestCase
{

    public function isEven($num)
    {
        return $num % 2 === 0;
    }

    /**
     * @test
     */
    public function partitionEmpty()
    {
        $xs = $this->createHotObservable([
            onNext(180, 5),
            onCompleted(210)
        ]);

        $observables = null;

        $s1 = null;
        $s2 = null;
        $r1 = $this->scheduler->createObserver();
        $r2 = $this->scheduler->createObserver();

        $this->scheduler->scheduleAbsolute(TestScheduler::CREATED, function () use (&$observables, $xs) {
            $observables = $xs->partition([$this, 'isEven']);
        });

        $this->scheduler->scheduleAbsolute(TestScheduler::SUBSCRIBED, function () use (&$observables, &$s1, &$s2, $r1, $r2) {
            $s1 = $observables[0]->subscribe($r1);
            $s2 = $observables[1]->subscribe($r2);
        });

        $this->scheduler->scheduleAbsolute(TestScheduler::DISPOSED, function () use (&$s1, &$s2) {
            $s1->dispose();
            $s2->dispose();
        });

        $this->scheduler->start();

        $this->assertMessages([onCompleted(210)], $r1->getMessages());
        $this->assertMessages([onCompleted(210)], $r2->getMessages());

        $this->assertSubscriptions(
            [
                subscribe(200, 210),
                subscribe(200, 210)
            ],
            $xs->getSubscriptions()
        );
    }

    /**
     * @test
     */
    public function partitionSingle()
    {
        $xs = $this->createHotObservable([
            onNext(180, 5),
            onNext(210, 4),
            onCompleted(220)
        ]);

        $observables = null;

        $s1 = null;
        $s2 = null;
        $r1 = $this->scheduler->createObserver();
        $r2 = $this->scheduler->createObserver();

        $this->scheduler->scheduleAbsolute(TestScheduler::CREATED, function () use (&$observables, $xs) {
            $observables = $xs->partition([$this, 'isEven']);
        });

        $this->scheduler->scheduleAbsolute(TestScheduler::SUBSCRIBED, function () use (&$observables, &$s1, &$s2, $r1, $r2) {
            $s1 = $observables[0]->subscribe($r1);
            $s2 = $observables[1]->subscribe($r2);
        });

        $this->scheduler->scheduleAbsolute(TestScheduler::DISPOSED, function () use (&$s1, &$s2) {
            $s1->dispose();
            $s2->dispose();
        });

        $this->scheduler->start();

        $this->assertMessages(
            [
                onNext(210, 4),
                onCompleted(220)
            ],
            $r1->getMessages()
        );

        $this->assertMessages(
            [
                onCompleted(220)
            ],
            $r2->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 220),
                subscribe(200, 220)
            ],
            $xs->getSubscriptions()
        );
    }

    /**
     * @test
     */
    public function partitionEach()
    {
        $xs = $this->createHotObservable([
            onNext(180, 5),
            onNext(210, 4),
            onNext(220, 3),
            onCompleted(230)
        ]);

        $observables = null;

        $s1 = null;
        $s2 = null;
        $r1 = $this->scheduler->createObserver();
        $r2 = $this->scheduler->createObserver();

        $this->scheduler->scheduleAbsolute(TestScheduler::CREATED, function () use (&$observables, $xs) {
            $observables = $xs->partition([$this, 'isEven']);
        });

        $this->scheduler->scheduleAbsolute(TestScheduler::SUBSCRIBED, function () use (&$observables, &$s1, &$s2, $r1, $r2) {
            $s1 = $observables[0]->subscribe($r1);
            $s2 = $observables[1]->subscribe($r2);
        });

        $this->scheduler->scheduleAbsolute(TestScheduler::DISPOSED, function () use (&$s1, &$s2) {
            $s1->dispose();
            $s2->dispose();
        });

        $this->scheduler->start();

        $this->assertMessages(
            [
                onNext(210, 4),
                onCompleted(230)
            ],
            $r1->getMessages()
        );

        $this->assertMessages(
            [
                onNext(220, 3),
                onCompleted(230)
            ],
            $r2->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 230),
                subscribe(200, 230)
            ],
            $xs->getSubscriptions()
        );
    }

    /**
     * @test
     */
    public function partitionCompleted()
    {
        $xs = $this->createHotObservable([
            onNext(180, 5),
            onNext(210, 4),
            onNext(240, 3),
            onNext(290, 2),
            onNext(350, 1),
            onCompleted(360)
        ]);

        $observables = null;

        $s1 = null;
        $s2 = null;
        $r1 = $this->scheduler->createObserver();
        $r2 = $this->scheduler->createObserver();

        $this->scheduler->scheduleAbsolute(TestScheduler::CREATED, function () use (&$observables, $xs) {
            $observables = $xs->partition([$this, 'isEven']);
        });

        $this->scheduler->scheduleAbsolute(TestScheduler::SUBSCRIBED, function () use (&$observables, &$s1, &$s2, $r1, $r2) {
            $s1 = $observables[0]->subscribe($r1);
            $s2 = $observables[1]->subscribe($r2);
        });

        $this->scheduler->scheduleAbsolute(TestScheduler::DISPOSED, function () use (&$s1, &$s2) {
            $s1->dispose();
            $s2->dispose();
        });

        $this->scheduler->start();

        $this->assertMessages(
            [
                onNext(210, 4),
                onNext(290, 2),
                onCompleted(360)
            ],
            $r1->getMessages()
        );

        $this->assertMessages(
            [
                onNext(240, 3),
                onNext(350, 1),
                onCompleted(360)
            ],
            $r2->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 360),
                subscribe(200, 360)
            ],
            $xs->getSubscriptions()
        );
    }

    /**
     * @test
     */
    public function partitionNotCompleted()
    {
        $xs = $this->createHotObservable([
            onNext(180, 5),
            onNext(210, 4),
            onNext(240, 3),
            onNext(290, 2),
            onNext(350, 1)
        ]);

        $observables = null;

        $s1 = null;
        $s2 = null;
        $r1 = $this->scheduler->createObserver();
        $r2 = $this->scheduler->createObserver();

        $this->scheduler->scheduleAbsolute(TestScheduler::CREATED, function () use (&$observables, $xs) {
            $observables = $xs->partition([$this, 'isEven']);
        });

        $this->scheduler->scheduleAbsolute(TestScheduler::SUBSCRIBED, function () use (&$observables, &$s1, &$s2, $r1, $r2) {
            $s1 = $observables[0]->subscribe($r1);
            $s2 = $observables[1]->subscribe($r2);
        });

        $this->scheduler->scheduleAbsolute(TestScheduler::DISPOSED, function () use (&$s1, &$s2) {
            $s1->dispose();
            $s2->dispose();
        });

        $this->scheduler->start();

        $this->assertMessages(
            [
                onNext(210, 4),
                onNext(290, 2)
            ],
            $r1->getMessages()
        );

        $this->assertMessages(
            [
                onNext(240, 3),
                onNext(350, 1)
            ],
            $r2->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 1000),
                subscribe(200, 1000)
            ],
            $xs->getSubscriptions()
        );
    }

    /**
     * @test
     */
    public function partitionError()
    {
        $error = new \Exception('error1');

        $xs = $this->createHotObservable([
            onNext(180, 5),
            onNext(210, 4),
            onNext(240, 3),
            onError(290, $error),
            onNext(350, 1),
            onCompleted(360)
        ]);

        $observables = null;

        $s1 = null;
        $s2 = null;
        $r1 = $this->scheduler->createObserver();
        $r2 = $this->scheduler->createObserver();

        $this->scheduler->scheduleAbsolute(TestScheduler::CREATED, function () use (&$observables, $xs) {
            $observables = $xs->partition([$this, 'isEven']);
        });

        $this->scheduler->scheduleAbsolute(TestScheduler::SUBSCRIBED, function () use (&$observables, &$s1, &$s2, $r1, $r2) {
            $s1 = $observables[0]->subscribe($r1);
            $s2 = $observables[1]->subscribe($r2);
        });

        $this->scheduler->scheduleAbsolute(TestScheduler::DISPOSED, function () use (&$s1, &$s2) {
            $s1->dispose();
            $s2->dispose();
        });

        $this->scheduler->start();

        $this->assertMessages(
            [
                onNext(210, 4),
                onError(290, $error)
            ],
            $r1->getMessages()
        );

        $this->assertMessages(
            [
                onNext(240, 3),
                onError(290, $error)
            ],
            $r2->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 290),
                subscribe(200, 290)
            ],
            $xs->getSubscriptions()
        );
    }

    /**
     * @test
     */
    public function partitionDisposed()
    {
        $xs = $this->createHotObservable([
            onNext(180, 5),
            onNext(210, 4),
            onNext(240, 3),
            onNext(290, 2),
            onNext(350, 1),
            onCompleted(360)
        ]);

        $observables = null;

        $s1 = null;
        $s2 = null;
        $r1 = $this->scheduler->createObserver();
        $r2 = $this->scheduler->createObserver();

        $this->scheduler->scheduleAbsolute(TestScheduler::CREATED, function () use (&$observables, $xs) {
            $observables = $xs->partition([$this, 'isEven']);
        });

        $this->scheduler->scheduleAbsolute(TestScheduler::SUBSCRIBED, function () use (&$observables, &$s1, &$s2, $r1, $r2) {
            $s1 = $observables[0]->subscribe($r1);
            $s2 = $observables[1]->subscribe($r2);
        });

        $this->scheduler->scheduleAbsolute(280, function () use (&$s1, &$s2) {
            $s1->dispose();
            $s2->dispose();
        });

        $this->scheduler->start();

        $this->assertMessages(
            [
                onNext(210, 4)
            ],
            $r1->getMessages()
        );

        $this->assertMessages(
            [
                onNext(240, 3)
            ],
            $r2->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 280),
                subscribe(200, 280)
            ],
            $xs->getSubscriptions()
        );
    }
}
