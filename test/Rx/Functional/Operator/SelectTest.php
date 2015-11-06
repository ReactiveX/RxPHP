<?php

namespace Rx\Functional\Operator;

use Exception;
use Rx\Functional\FunctionalTestCase;
use Rx\Observable\ReturnObservable;


class SelectTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function calls_on_error_if_selector_throws_an_exception()
    {
        $xs = $this->createHotObservable(array(
            onNext(500, 42),
        ));

        $results = $this->scheduler->startWithCreate(function() use ($xs) {
            return $xs->select(function() { throw new Exception(); });
        });

        $this->assertMessages(array(onError(500, new Exception())), $results->getMessages());
    }

    /**
     * @test
     */
    public function select_calls_on_completed()
    {
        $xs = $this->createHotObservable(array(
            onCompleted(500),
        ));

        $results = $this->scheduler->startWithCreate(function() use ($xs) {
            return $xs->select('RxIdentity');
        });

        $this->assertMessages(array(onCompleted(500)), $results->getMessages());
    }

    /**
     * @test
     */
    public function select_calls_on_error()
    {
        $xs = $this->createHotObservable(array(
            onError(500, new Exception()),
        ));

        $results = $this->scheduler->startWithCreate(function() use ($xs) {
            return $xs->select('RxIdentity');
        });

        $this->assertMessages(array(onError(500, new Exception())), $results->getMessages());
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
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
        $xs = $this->createHotObservable(array(
            onNext(100,  2),
            onNext(300, 21),
            onNext(500, 42),
            onNext(800, 84),
            onCompleted(820),
        ));

        $results = $this->scheduler->startWithCreate(function() use ($xs) {
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
