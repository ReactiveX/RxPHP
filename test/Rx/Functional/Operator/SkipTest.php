<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;


use Rx\Functional\FunctionalTestCase;
use Rx\Observable\ReturnObservable;
use Rx\Observer\CallbackObserver;


class SkipTest extends FunctionalTestCase
{
    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function it_throws_an_exception_on_negative_amounts()
    {
        $observable = new ReturnObservable(42, $this->scheduler);
        $result     = $observable->skip(-1);

        $result->subscribe(new CallbackObserver());
    }

    /**
     * @test
     */
    public function it_passes_on_complete()
    {
        $xs = $this->createHotObservable(array(
            onNext(300, 21),
            onNext(500, 42),
            onNext(800, 84),
            onCompleted(820),
        ));

        $results = $this->scheduler->startWithCreate(function() use ($xs) {
            return $xs->skip(0);
        });

        $this->assertMessages(array(
            onNext(300, 21),
            onNext(500, 42),
            onNext(800, 84),
            onCompleted(820),
        ), $results->getMessages());
    }

    /**
     * @test
     */
    public function it_skips_one_value()
    {
        $scheduler = $this->createTestScheduler();
        $xs        = $this->createHotObservable(array(
            onNext(300, 21),
            onNext(500, 42),
            onNext(800, 84),
            onCompleted(820),
        ));

        $results = $this->scheduler->startWithCreate(function() use ($xs) {
            return $xs->skip(1);
        });

        $this->assertMessages(array(
            onNext(500, 42),
            onNext(800, 84),
            onCompleted(820),
        ), $results->getMessages());
    }

    /**
     * @test
     */
    public function it_skips_multiple_values()
    {
        $scheduler = $this->createTestScheduler();
        $xs        = $this->createHotObservable(array(
            onNext(300, 21),
            onNext(500, 42),
            onNext(800, 84),
            onNext(850, 168),
            onCompleted(870),
        ));

        $results = $this->scheduler->startWithCreate(function() use ($xs) {
            return $xs->skip(2);
        });

        $this->assertMessages(array(
            onNext(800, 84),
            onNext(850, 168),
            onCompleted(870),
        ), $results->getMessages());
    }
}
