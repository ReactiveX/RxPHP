<?php

declare(strict_types = 1);

namespace Rx\Functional\Observable;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable\IntervalObservable;
use Rx\Observer\CallbackObserver;

class IntervalObservableTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function interval_relative_time_basic()
    {
        $results = $this->scheduler->startWithCreate(function () {
            return new IntervalObservable(100, $this->scheduler);
        });

        $this->assertMessages(
            [
                onNext(300, 0),
                onNext(400, 1),
                onNext(500, 2),
                onNext(600, 3),
                onNext(700, 4),
                onNext(800, 5),
                onNext(900, 6)
            ],
            $results->getMessages()
        );
    }

    /**
     * @test
     */
    public function interval_relative_time_zero()
    {
        $results = $this->scheduler->startWithDispose(function () {
            return new IntervalObservable(0, $this->scheduler);
        }, 210);

        $this->assertMessages(
            [
                onNext(201, 0),
                onNext(202, 1),
                onNext(203, 2),
                onNext(204, 3),
                onNext(205, 4),
                onNext(206, 5),
                onNext(207, 6),
                onNext(208, 7),
                onNext(209, 8)
            ],
            $results->getMessages()
        );
    }

    /**
     * @test
     */
    public function interval_relative_time_Negative()
    {
        $results = $this->scheduler->startWithDispose(function () {
            return new IntervalObservable(-1, $this->scheduler);
        }, 210);

        $this->assertMessages(
            [
                onNext(201, 0),
                onNext(202, 1),
                onNext(203, 2),
                onNext(204, 3),
                onNext(205, 4),
                onNext(206, 5),
                onNext(207, 6),
                onNext(208, 7),
                onNext(209, 8)
            ],
            $results->getMessages()
        );
    }

    /**
     * @test
     */
    public function interval_relative_time_disposed()
    {
        $results = $this->scheduler->startWithCreate(function () {
            return new IntervalObservable(1000, $this->scheduler);
        });

        $this->assertMessages([], $results->getMessages());
    }

    /**
     * @test
     * @expectedException \Exception
     */
    public function interval_relative_time_observer_throws()
    {
        $xs = new IntervalObservable(1, $this->scheduler);

        $xs->subscribe(new CallbackObserver(function () {
            throw new \Exception();
        }));

        $this->scheduler->start();
    }
}
