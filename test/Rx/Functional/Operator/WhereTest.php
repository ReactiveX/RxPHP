<?php

namespace Rx\Functional\Operator;

use Exception;
use Rx\Functional\FunctionalTestCase;
use Rx\Testing\HotObservable;
use Rx\Testing\TestScheduler;
use Rx\Observable\ReturnObservable;

class WhereTest extends FunctionalTestCase
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

    /**
     * @test
     */
    public function calls_on_error_if_predicate_throws_an_exception()
    {
        $observable = new ReturnObservable(1);

        $called = false;
        $observable->where(function() { throw new Exception(); })
            ->subscribeCallback(function() {}, function($ex) use (&$called) { $called = true; });

        $this->assertTrue($called);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function it_throws_an_exception_if_predicate_is_not_a_callable()
    {
        $observable = new ReturnObservable(1);
        $observable->where(42);
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
}
