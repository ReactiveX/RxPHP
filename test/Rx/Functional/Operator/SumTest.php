<?php

namespace Rx\Functional\Operator;


use Rx\Functional\FunctionalTestCase;
use Rx\Observable;
use Rx\Observable\ReturnObservable;
use Rx\Observer\CallbackObserver;
use Rx\Operator\SumOperator;


class SumTest extends FunctionalTestCase
{
    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function it_throws_an_exception_on_invalid_resolver()
    {
        $observable = new ReturnObservable(42);
        $result = $observable->sum(255);

        $result->subscribe(new CallbackObserver());
    }

    /**
     * @test
     * @expectedException \UnexpectedValueException
     */
    public function exception_on_not_numeric_with_strict_resolver()
    {
        $xs = array(1, 2, "2a", 3,);
        $result = Observable::fromArray($xs)->sum();

        $result->subscribe(new CallbackObserver());
    }

    /**
     * @test
     * @expectedException \UnexpectedValueException
     */
    public function exception_on_array_with_strict_resolver()
    {
        $xs = array(1, 2, array(), 3,);
        $result = Observable::fromArray($xs)->sum();

        $result->subscribe(new CallbackObserver());
    }

    /**
     * @test
     * @expectedException \UnexpectedValueException
     */
    public function exception_boolean_with_strict_resolver()
    {
        $xs = array(1, 2, true, 3,);
        $result = Observable::fromArray($xs)->sum();

        $result->subscribe(new CallbackObserver());
    }

    /**
     * @test
     */
    public function it_passes_on_complete_with_strict_resolver()
    {
        $xs = $this->createHotObservable(array(
            onNext(210, 1e1),
            onNext(220, "2"),
            onNext(230, 3),
            onNext(240, 5),
            onNext(250, 7),
            onCompleted(300),
        ));

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->sum();
        });

        $this->assertMessages(array(
            onNext(300, 27),
            onCompleted(300),
        ), $results->getMessages());
    }

    /**
     * @test
     */
    public function it_pass_zero_on_empty_sequence()
    {
        $xs = $this->createHotObservable(array(
            onCompleted(300),
        ));

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->sum();
        });

        $this->assertMessages(array(
            onNext(300, 0),
            onCompleted(300),
        ), $results->getMessages());
    }

    /**
     * @test
     */

    public function it_passes_on_complete_with_cast_resolver()
    {


        $xs = $this->createHotObservable(array(
            onNext(210, 1e1),
            onNext(220, "2a"),
            onNext(230, true),
            onNext(240, false),
            onNext(260, null),
            onCompleted(300),
        ));

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->sum(SumOperator::UNEXPECTED_VALUE_CAST);
        });

        $this->assertMessages(array(
            onNext(300, 13),
            onCompleted(300),
        ), $results->getMessages());
    }


    /**
     * @test
     */
    public function it_passes_on_complete_with_ignore_resolver()
    {
        $xs = $this->createHotObservable(array(
            onNext(210, 1e1),
            onNext(220, "2a"),
            onNext(230, true),
            onNext(240, 5),
            onNext(250, 7),
            onCompleted(300),
        ));

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->sum(SumOperator::UNEXPECTED_VALUE_IGNORE);
        });

        $this->assertMessages(array(
            onNext(300, 22),
            onCompleted(300),
        ), $results->getMessages());
    }
}