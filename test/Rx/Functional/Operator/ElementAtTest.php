<?php

namespace Rx\Functional\Operator;


use Rx\Functional\FunctionalTestCase;
use Rx\Observable\ReturnObservable;
use Rx\Observer\CallbackObserver;


class ElementAtTest extends FunctionalTestCase
{
    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function it_throws_an_exception_on_negative_index()
    {
        $observable = new ReturnObservable(42);
        $result     = $observable->elementAt(-1);

        $result->subscribe(new CallbackObserver());
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function it_throws_an_exception_on_not_int_index()
    {
        $observable = new ReturnObservable(42);
        $result     = $observable->elementAt("a");

        $result->subscribe(new CallbackObserver());
    }

    /**
     * @test
     */
    public function it_calls_on_complete_after_value_at_index()
    {
        $xs = $this->createHotObservable(array(
            onNext(300, 21),
            onNext(500, 42),
            onNext(600, 14),
            onNext(700, 67),
            onNext(800, 84),
            onCompleted(820),
        ));

        $results = $this->scheduler->startWithCreate(function() use ($xs) {
            return $xs->elementAt(2);
        });

        $this->assertMessages(array(
            onNext(600, 14),
            onCompleted(600),
        ), $results->getMessages());
    }

    /**
     * @test
     */
    public function take_zero_calls_when_index_greater_than_sequence_length()
    {
        $xs        = $this->createHotObservable(array(
            onNext(250, 21),
            onNext(300, 42),
            onNext(400, 84),
            onCompleted(420),
        ));

        $results = $this->scheduler->startWithCreate(function() use ($xs) {
            return $xs->elementAt(3);
        });

        $this->assertMessages(array(
            onCompleted(420),
        ), $results->getMessages());
    }

    public function take_zero_calls_on_empty_sequence()
    {
        $xs        = $this->createHotObservable(array(
            onCompleted(220),
        ));

        $results = $this->scheduler->startWithCreate(function() use ($xs) {
            return $xs->elementAt(3);
        });

        $this->assertMessages(array(
            onCompleted(220),
        ), $results->getMessages());
    }
}
