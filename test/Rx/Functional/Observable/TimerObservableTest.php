<?php

declare(strict_types = 1);

namespace Rx\Functional\Observable;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable;
use Rx\Observable\TimerObservable;
use Rx\Testing\TestScheduler;

class TimerObservableTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function timer_one_shot_relative_time_basic()
    {
        $results = $this->scheduler->startWithCreate(function () {
            return new TimerObservable(300, $this->scheduler);
        });

        $this->assertMessages(
            [
                onNext(500, 0),
                onCompleted(500)
            ],
            $results->getMessages()
        );
    }

    /**
     * @test
     */
    public function timer_one_shot_relative_time_zero()
    {
        $results = $this->scheduler->startWithCreate(function () {
            return new TimerObservable(0, $this->scheduler);
        });

        $this->assertMessages(
            [
                onNext(201, 0),
                onCompleted(201)
            ],
            $results->getMessages()
        );
    }

    /**
     * @test
     * @expectedException \TypeError
     */
    public function timer_one_shot_relative_time_zero_non_int()
    {
        $this->scheduler->startWithCreate(function () {
            return Observable::timer('z', $this->scheduler);
        });
    }

    /**
     * @test
     */
    public function timer_one_shot_relative_time_negative()
    {
        $results = $this->scheduler->startWithCreate(function () {
            return new TimerObservable(-1, $this->scheduler);
        });

        $this->assertMessages(
            [
                onNext(201, 0),
                onCompleted(201)
            ],
            $results->getMessages()
        );
    }

    /**
     * @test
     */
    public function timer_one_shot_relative_time_disposed()
    {
        $results = $this->scheduler->startWithCreate(function () {
            return new TimerObservable(1000, $this->scheduler);
        });

        $this->assertMessages([], $results->getMessages());
    }

    /**
     * @test
     */
    public function timer_one_shot_relative_time_dispose_before_dueTime()
    {
        $results = $this->scheduler->startWithDispose(function () {
            return new TimerObservable(500, $this->scheduler);
        }, 400);

        $this->assertMessages([], $results->getMessages());
    }

    /**
     * @test
     */
    public function timer_one_shot_relative_time_throws()
    {

        $scheduler1 = new TestScheduler();

        $xs = Observable::timer(1, $scheduler1);
        $xs->subscribe(function () {
            throw new \Exception();
        });

        $this->assertException(function () use ($scheduler1) {
            $scheduler1->start();
        });

        $scheduler2 = new TestScheduler();

        $ys = Observable::timer(1, $scheduler2);
        $ys->subscribe(null, null, function () {
            throw new \Exception();
        });

        $this->assertException(function () use ($scheduler2) {
            $scheduler2->start();
        });
    }
}
