<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Rx\Disposable\EmptyDisposable;
use Rx\Functional\FunctionalTestCase;
use Rx\Observable;
use Rx\Scheduler\ImmediateScheduler;
use Rx\SchedulerInterface;

class ThrottleTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function throttle_completed()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onNext(250, 3),
            onNext(310, 4),
            onNext(350, 5),
            onNext(410, 6),
            onNext(450, 7),
            onCompleted(500)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->throttle(200, $this->scheduler);
        });

        $this->assertMessages([
            onNext(210, 2),
            onNext(410, 6),
            onNext(610, 7),
            onCompleted(610)
        ], $results->getMessages());
        
        $this->assertSubscriptions([
            subscribe(200, 610)
        ], $xs->getSubscriptions());
    }
    
    /**
     * @test
     */
    public function throttle_never()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->throttle(200, $this->scheduler);
        });

        $this->assertMessages([], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 1000)
        ], $xs->getSubscriptions());
    }
    
    /**
     * @test
     */
    public function throttle_empty()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(500)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->throttle(200, $this->scheduler);
        });

        $this->assertMessages([
            onCompleted(500)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 500)
        ], $xs->getSubscriptions());
    }
    
    /**
     * @test
     */
    public function throttle_error()
    {
        $error = new \Exception();

        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onNext(250, 3),
            onNext(310, 4),
            onNext(350, 5),
            onError(410, $error),
            onNext(450, 7),
            onCompleted(500)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->throttle(200, $this->scheduler);
        });

        $this->assertMessages([
            onNext(210, 2),
            onError(410, $error)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 410)
        ], $xs->getSubscriptions());
    }
    
    /**
     * @test
     */
    public function throttle_no_end()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onNext(250, 3),
            onNext(310, 4),
            onNext(350, 5),
            onNext(410, 6),
            onNext(450, 7)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->throttle(200, $this->scheduler);
        });

        $this->assertMessages([
            onNext(210, 2),
            onNext(410, 6),
            onNext(610, 7)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 1000)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function throttle_dispose()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onNext(250, 3),
            onNext(310, 4),
            onNext(350, 5),
            onNext(410, 6),
            onNext(450, 7)
        ]);

        $results = $this->scheduler->startWithDispose(function () use ($xs) {
            return $xs->throttle(200, $this->scheduler);
        }, 420);

        $this->assertMessages([
            onNext(210, 2),
            onNext(410, 6)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 420)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function throttle_dispose_with_value_waiting()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onNext(250, 3),
            onNext(310, 4),
            onNext(350, 5),
            onNext(410, 6),
            onNext(450, 7)
        ]);

        $results = $this->scheduler->startWithDispose(function () use ($xs) {
            return $xs->throttle(200, $this->scheduler);
        }, 460);

        $this->assertMessages([
            onNext(210, 2),
            onNext(410, 6)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 460)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function throttle_quiet_observable_emits_immediately()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(250, 2),
            onNext(550, 7)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->throttle(200, $this->scheduler);
        });

        $this->assertMessages([
            onNext(250, 2),
            onNext(550, 7)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 1000)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function throttle_noisy_observable_drops_items()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(250, 2),
            onNext(251, 3),
            onNext(252, 4),
            onNext(253, 5),
            onNext(254, 6),
            onNext(255, 7),
            onNext(256, 8),
            onNext(257, 9),
            onNext(550, 10),
            onNext(850, 11)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->throttle(200, $this->scheduler);
        });

        $this->assertMessages([
            onNext(250, 2),
            onNext(450, 9),
            onNext(650, 10),
            onNext(850, 11)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 1000)
        ], $xs->getSubscriptions());
    }
    
    /**
     * @test
     */
    public function throttle_scheduler_overrides_subscribe_scheduler()
    {
        $scheduler = $this->createMock(SchedulerInterface::class);
        $scheduler->expects($this->any())
            ->method('schedule')
            ->willReturn(new EmptyDisposable());
        
        Observable::of(1, $scheduler)
            ->throttle(100, $scheduler)
            ->subscribe();
    }
}
