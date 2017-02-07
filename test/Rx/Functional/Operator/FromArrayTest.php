<?php

declare(strict_types = 1);

namespace Rx\Functional;

use Rx\Observable;

class FromArrayTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function it_schedules_all_elements_from_the_array()
    {
        $xs = Observable::fromArray(['foo', 'bar', 'baz'], $this->scheduler);

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
        $xs = Observable::fromArray([], $this->scheduler);

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
        $xs = Observable::fromArray([1], $this->scheduler);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs;
        });

        $this->assertMessages([
            onNext(201, 1),
            onCompleted(202),
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function fromArray_dispose()
    {
        $xs = Observable::fromArray(['foo', 'bar', 'baz'], $this->scheduler);

        $results = $this->scheduler->startWithDispose(function () use ($xs) {
            return $xs;
        }, 202);

        $this->assertMessages([
            onNext(201, 'foo')
        ], $results->getMessages());
    }
}
