<?php

declare(strict_types=1);

namespace Rx\Functional\Operator;

use Rx\Disposable\CompositeDisposable;
use Rx\Disposable\SerialDisposable;
use Rx\Functional\FunctionalTestCase;

class SingleInstanceTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function singleInstance_basic()
    {
        $xs = $this->createColdObservable([
            onNext(100, 1),
            onNext(150, 2),
            onNext(200, 3),
            onCompleted(250)
        ]);

        $ys = null;
        $results1 = $this->scheduler->createObserver();
        $results2 = $this->scheduler->createObserver();
        $disposable = null;

        $this->scheduler->scheduleAbsolute($this->scheduler::CREATED, function () use (&$ys, $xs) {
            $ys = $xs->singleInstance();
        });

        $this->scheduler->scheduleAbsolute($this->scheduler::SUBSCRIBED, function () use (&$ys, &$disposable, $results1, $results2) {
            $disposable = new CompositeDisposable([
                $ys->subscribe($results1),
                $ys->subscribe($results2)
            ]);
        });

        $this->scheduler->scheduleAbsolute($this->scheduler::DISPOSED, function () use (&$disposable) {
            $disposable->dispose();
        });

        $this->scheduler->start();

        $this->assertMessages([
            onNext(300, 1),
            onNext(350, 2),
            onNext(400, 3),
            onCompleted(450)
        ], $results1->getMessages());

        $this->assertMessages([
            onNext(300, 1),
            onNext(350, 2),
            onNext(400, 3),
            onCompleted(450)
        ], $results2->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 450)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function singleInstance_subscribe_after_stopped()
    {
        $xs = $this->createColdObservable([
            onNext(100, 1),
            onNext(150, 2),
            onNext(200, 3),
            onCompleted(250)
        ]);

        $ys = null;
        $results1 = $this->scheduler->createObserver();
        $results2 = $this->scheduler->createObserver();
        $disposable = new SerialDisposable();

        $this->scheduler->scheduleAbsolute(100, function () use (&$ys, $xs) {
            $ys = $xs->singleInstance();
        });

        $this->scheduler->scheduleAbsolute(200, function () use (&$ys, $disposable, $results1) {
            $disposable->setDisposable($ys->subscribe($results1));
        });

        $this->scheduler->scheduleAbsolute(600, function () use (&$ys, $disposable, $results2) {
            $disposable->setDisposable($ys->subscribe($results2));
        });

        $this->scheduler->scheduleAbsolute(900, function () use (&$disposable) {
            $disposable->dispose();
        });

        $this->scheduler->start();

        $this->assertMessages([
            onNext(300, 1),
            onNext(350, 2),
            onNext(400, 3),
            onCompleted(450)
        ], $results1->getMessages());

        $this->assertMessages([
            onNext(700, 1),
            onNext(750, 2),
            onNext(800, 3),
            onCompleted(850)
        ], $results2->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 450),
            subscribe(600, 850)
        ], $xs->getSubscriptions());
    }
}
