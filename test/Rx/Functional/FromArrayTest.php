<?php

namespace Rx\Functional;

use Exception;
use Rx\Observable\BaseObservable;
use Rx\Testing\MockObserver;

class FromArrayTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function it_schedules_all_elements_from_the_array()
    {
        $xs = BaseObservable::fromArray(array('foo', 'bar', 'baz'), $this->scheduler);

        $results = $this->scheduler->startWithCreate(function() use ($xs) {
            return $xs;
        });

        $this->assertCount(4, $results->getMessages());
        $this->assertMessages(array(
            onNext(201, 'foo'),
            onNext(202, 'bar'),
            onNext(203, 'baz'),
            onCompleted(204),
        ), $results->getMessages());
    }
    /**
     * @test
     */
    public function it_calls_on_complete_when_the_array_is_empte()
    {
        $xs = BaseObservable::fromArray(array(), $this->scheduler);

        $results = $this->scheduler->startWithCreate(function() use ($xs) {
            return $xs;
        });

        $this->assertCount(1, $results->getMessages());
        $this->assertMessages(array(
            onCompleted(201),
        ), $results->getMessages());
    }
}
