<?php

namespace Rx\Functional;

use Exception;
use Rx\TestCase;
use Rx\Testing\HotObservable;
use Rx\Testing\Recorded;
use Rx\Testing\Subscription;
use Rx\Testing\TestScheduler;

class WhereOperatorTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function it_filters_all_on_false()
    {
        $scheduler = $this->createTestScheduler();
        $xs        = $this->createHotObservableWithData($scheduler);

        $results = $scheduler->startWithCreate(function() use ($xs) {
            return $xs->where(function($elem) { return false; });
        });


        $this->assertMessages(array(
            onCompleted(820),
        ), $results->getMessages());
    }

    /**
     * @test
     */
    public function it_passes_all_on_true() 
    {
        $scheduler = $this->createTestScheduler();
        $xs        = $this->createHotObservableWithData($scheduler);

        $results = $scheduler->startWithCreate(function() use ($xs) {
            return $xs->where(function($elem) { return true; });
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
    public function it_passes_on_error()
    {
        $exception = new Exception();
        $scheduler  = new TestScheduler();
        $xs = new HotObservable($scheduler, array(
            onNext(500, 42),
            onError(820, $exception),
        ));

        $results = $scheduler->startWithCreate(function() use ($xs) {
            return $xs->where(function($elem) { return $elem === 42; });
        });


        $this->assertMessages(array(
            onNext(500, 42),
            onError(820, $exception),
        ), $results->getMessages());
    }

    protected function createHotObservableWithData($scheduler)
    {
        return new HotObservable($scheduler, array(
            onNext(100,  2),
            onNext(300, 21),
            onNext(500, 42),
            onNext(800, 84),
            onCompleted(820),
        ));
    }

    protected function createTestScheduler()
    {
        return new TestScheduler();
    }
}
