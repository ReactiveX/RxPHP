<?php

namespace Rx\Functional\Observable;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable\IteratorObservable;

class IteratorObservableTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function it_schedules_all_elements_from_the_generator()
    {
        $generator = $this->genOneToThree();

        $xs = new IteratorObservable($generator);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs;
        });

        $this->assertMessages([
            onNext(201, 1),
            onNext(202, 2),
            onNext(203, 3),
            onCompleted(204),
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function generator_yields_null()
    {
        $generator = $this->genNull();

        $xs = new IteratorObservable($generator);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs;
        });

        $this->assertMessages([
            onNext(201, null),
            onCompleted(202),
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function generator_yields_one()
    {
        $generator = $this->genOne();

        $xs = new IteratorObservable($generator);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs;
        });

        $this->assertMessages([
            onNext(200, 1),
            onCompleted(202),
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function generator_throws_error()
    {
        $error     = new \Exception();
        $generator = $this->genError($error);

        $xs = new IteratorObservable($generator);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs;
        });

        $this->assertMessages([
            onError(201, $error)
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function generator_dispose()
    {
        $generator = $this->genOneToThree();

        $xs = new IteratorObservable($generator);

        $results = $this->scheduler->startWithDispose(function () use ($xs) {
            return $xs;
        }, 202);

        $this->assertMessages([
            onNext(201, 1)
        ], $results->getMessages());
    }

    private function genOneToThree()
    {
        for ($i = 1; $i <= 3; $i++) {
            yield $i;
        }
    }

    private function genNull()
    {
        yield;
    }

    private function genOne()
    {
        yield 1;
    }

    private function genError(\Exception $e)
    {
        throw $e;
        yield;
    }
}
