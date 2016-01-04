<?php

namespace Rx\Functional;

use Rx\Observable\BaseObservable;

class FromArrayTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function it_schedules_all_elements_from_the_array()
    {
        $xs = BaseObservable::fromArray(['foo', 'bar', 'baz']);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs;
        });

        $this->assertMessages([
            onNext(201, 'foo'),
            onNext(202, 'bar'),
            onNext(203, 'baz'),
            onCompleted(204),
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function it_calls_on_complete_when_the_array_is_empty()
    {
        $xs = BaseObservable::fromArray([]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs;
        });

        $this->assertMessages([
            onCompleted(201),
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function fromArray_one()
    {
        $xs = BaseObservable::fromArray([1]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs;
        });

        $this->assertMessages([
            onNext(201, 1),
            onCompleted(202),
        ], $results->getMessages());
    }
}
