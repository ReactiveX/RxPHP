<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable\AnonymousObservable;
use Rx\Observable\ErrorObservable;
use Rx\Observable\ReturnObservable;
use Rx\Observer\CallbackObserver;
use Rx\Testing\TestScheduler;

class RepeatTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function repeat_Observable_basic(): void
    {
        $xs = $this->createColdObservable([
            onNext(100, 1),
            onNext(150, 2),
            onNext(200, 3),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->repeat();
        });

        $this->assertMessages(
            [
                onNext(300, 1),
                onNext(350, 2),
                onNext(400, 3),
                onNext(550, 1),
                onNext(600, 2),
                onNext(650, 3),
                onNext(800, 1),
                onNext(850, 2),
                onNext(900, 3)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 450),
                subscribe(450, 700),
                subscribe(700, 950),
                subscribe(950, 1000)
            ],
            $xs->getSubscriptions()
        );
    }

    /**
     * @test
     */
    public function repeat_Observable_infinite(): void
    {
        $xs = $this->createColdObservable([
            onNext(100, 1),
            onNext(150, 2),
            onNext(200, 3)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->repeat();
        });

        $this->assertMessages(
            [
                onNext(300, 1),
                onNext(350, 2),
                onNext(400, 3)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 1000)
            ],
            $xs->getSubscriptions()
        );
    }

    /**
     * @test
     */
    public function repeat_Observable_error(): void
    {
        $error = new \Exception();

        $xs = $this->createColdObservable([
            onNext(100, 1),
            onNext(150, 2),
            onNext(200, 3),
            onError(250, $error)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->repeat();
        });

        $this->assertMessages(
            [
                onNext(300, 1),
                onNext(350, 2),
                onNext(400, 3),
                onError(450, $error)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 450)
            ],
            $xs->getSubscriptions()
        );
    }

    /**
     * @test
     */
    public function repeat_Observable_throws_1(): void
    {
        $this->expectException(\Exception::class);
        $scheduler1 = new TestScheduler();

        $xs = (new ReturnObservable(1, $scheduler1))->repeat();

        $xs->subscribe(new CallbackObserver(
            function ($x): void {
                throw new \Exception();
            }
        ));

        $scheduler1->start();
    }

    /**
     * @test
     */
    public function repeat_Observable_throws_2(): void
    {
        $this->expectException(\Exception::class);
        $scheduler2 = new TestScheduler();

        $xs = (new ErrorObservable(new \Exception(), $scheduler2))->repeat();

        $xs->subscribe(new CallbackObserver(
            null,
            function ($x): void {
                throw new \Exception();
            }
        ));

        $scheduler2->start();
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function repeat_Observable_throws_3(): void
    {
        $scheduler3 = new TestScheduler();
        $xs         = (new ReturnObservable(1, $scheduler3))->repeat();

        $disp = $xs->subscribe(new CallbackObserver(
            null,
            null,
            function (): void {
                throw new \Exception;
            }
        ));

        $scheduler3->scheduleAbsolute(210, function () use ($disp): void {
            $disp->dispose();
        });

        $scheduler3->start();
    }

    /**
     * @test
     */
    public function repeat_Observable_throws_4(): void
    {
        $this->expectException(\Exception::class);
        $xs = (new AnonymousObservable(function (): void {
            throw new \Exception;
        }))->repeat();

        $xs->subscribe(new CallbackObserver());
    }

    /**
     * @test
     */
    public function repeat_Observable_repeat_count_basic(): void
    {
        $xs = $this->createColdObservable([
            onNext(5, 1),
            onNext(10, 2),
            onNext(15, 3),
            onCompleted(20)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->repeat(3);
        });

        $this->assertMessages(
            [
                onNext(205, 1),
                onNext(210, 2),
                onNext(215, 3),
                onNext(225, 1),
                onNext(230, 2),
                onNext(235, 3),
                onNext(245, 1),
                onNext(250, 2),
                onNext(255, 3),
                onCompleted(260)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 220),
                subscribe(220, 240),
                subscribe(240, 260)
            ],
            $xs->getSubscriptions()
        );
    }

    /**
     * @test
     */
    public function repeat_Observable_repeat_count_dispose(): void
    {
        $xs = $this->createColdObservable([
            onNext(5, 1),
            onNext(10, 2),
            onNext(15, 3),
            onCompleted(20)
        ]);

        $results = $this->scheduler->startWithDispose(function () use ($xs) {
            return $xs->repeat(3);
        }, 231);

        $this->assertMessages(
            [
                onNext(205, 1),
                onNext(210, 2),
                onNext(215, 3),
                onNext(225, 1),
                onNext(230, 2)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 220),
                subscribe(220, 231)
            ],
            $xs->getSubscriptions()
        );
    }

    /**
     * @test
     */
    public function repeat_Observable_repeat_count_infinite(): void
    {
        $xs = $this->createColdObservable([
            onNext(100, 1),
            onNext(150, 2),
            onNext(200, 3)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->repeat(3);
        });

        $this->assertMessages(
            [
                onNext(300, 1),
                onNext(350, 2),
                onNext(400, 3)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 1000)
            ],
            $xs->getSubscriptions()
        );
    }

    /**
     * @test
     */
    public function repeat_Observable_repeat_count_error(): void
    {
        $error = new \Exception();

        $xs = $this->createColdObservable([
            onNext(100, 1),
            onNext(150, 2),
            onNext(200, 3),
            onError(250, $error)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->repeat(3);
        });

        $this->assertMessages(
            [
                onNext(300, 1),
                onNext(350, 2),
                onNext(400, 3),
                onError(450, $error)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions(
            [
                subscribe(200, 450)
            ],
            $xs->getSubscriptions()
        );
    }

    /**
     * @test
     */
    public function repeat_Observable_repeat_count_throws_1(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('from onNext');
        $scheduler1 = new TestScheduler();
        $xs         = (new ReturnObservable(1, $scheduler1))->repeat(3);

        $xs->subscribe(new CallbackObserver(
            function ($x): void {
                throw new \Exception('from onNext');
            }
        ));

        $scheduler1->start();
    }

    /**
     * @test
     */
    public function repeat_Observable_repeat_count_throws_2(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('from onError');
        $scheduler2 = new TestScheduler();

        $xs = (new ErrorObservable(new \Exception('from ErrorObservable'), $scheduler2))->repeat(3);

        $xs->subscribe(new CallbackObserver(
            null,
            function ($x): void {
                throw new \Exception('from onError');
            }
        ));

        $scheduler2->start();
    }

    /**
     * @test
     */
    public function repeat_Observable_repeat_count_throws_3(): void
    {
        $this->expectException(\Exception::class);
        $scheduler3 = new TestScheduler();

        $xs = (new ReturnObservable(1, $scheduler3))->repeat(3);

        $xs->subscribe(new CallbackObserver(
            null,
            null,
            function (): void {
                throw new \Exception('from onCompleted');
            }
        ));

        $scheduler3->start();
    }

    /**
     * @test
     */
    public function repeat_Observable_repeat_count_throws_4(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('from Anon');
        $xss = (new AnonymousObservable(function (): void {
            throw new \Exception('from Anon');
        }))->repeat(3);

        $xss->subscribe(new CallbackObserver());
    }

    /**
     * @test
     */
    public function repeat_returns_empty_when_count_is_zero(): void
    {
        $xs = $this->createColdObservable([
            onNext(5, 1),
            onNext(10, 2),
            onNext(15, 3),
            onCompleted(20)
        ]);

        $result = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->repeat(0);
        });

        $this->assertMessages([
            onCompleted(200)
        ], $result->getMessages());

        $this->assertSubscriptions([], $xs->getSubscriptions());
    }
}
