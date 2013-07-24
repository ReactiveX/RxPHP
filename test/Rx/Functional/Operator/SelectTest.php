<?php

namespace Rx\Functional\Operator;

use Exception;
use Rx\Functional\FunctionalTestCase;
use Rx\Testing\HotObservable;
use Rx\Testing\TestScheduler;
use Rx\Observable\ReturnObservable;
use Rx\Observable\EmptyObservable;
use Rx\Observable\ThrowObservable;

class SelectTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function exception_thrown_in_callable_is_not_catched()
    {
        $observable = new ReturnObservable(1);

        $called = false;
        $observable->select(function() { throw new Exception(); })
            ->subscribeCallback(function() {}, function($ex) use (&$called) { $called = true; });

        $this->assertTrue($called);
    }

    /**
     * @test
     */
    public function select_calls_on_completed()
    {
        $observable = new EmptyObservable();

        $called = false;
        $observable->select('RxIdentity')
            ->subscribeCallback(function() {}, function() {}, function() use (&$called) { $called = true; });

        $this->assertTrue($called);
    }

    /**
     * @test
     */
    public function select_calls_on_error()
    {
        $observable = new ThrowObservable(new Exception);

        $called = false;
        $observable->select('RxIdentity')
            ->subscribeCallback(function() {}, function() use (&$called) { $called = true; });

        $this->assertTrue($called);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function select_expects_a_callable()
    {
        $observable = new ReturnObservable(1);
        $observable->select(42);
    }

    /**
     * @test
     */
    public function select_calls_selector()
    {
        $scheduler = $this->createTestScheduler();
        $xs        = new HotObservable($scheduler, array(
            onNext(100,  2),
            onNext(300, 21),
            onNext(500, 42),
            onNext(800, 84),
            onCompleted(820),
        ));

        $results = $scheduler->startWithCreate(function() use ($xs) {
            return $xs->select(function($elem) { return $elem * 2; });
        });

        $this->assertMessages(array(
            onNext(300,  42),
            onNext(500,  84),
            onNext(800, 168),
            onCompleted(820),
        ), $results->getMessages());

    }
}
