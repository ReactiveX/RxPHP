<?php

declare(strict_types=1);

namespace Rx\Functional\Observable;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable;
use Rx\Observable\IteratorObservable;
use Rx\Testing\MockObserver;

class IteratorObservableTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function it_schedules_all_elements_from_the_generator()
    {
        $generator = $this->genOneToThree();

        $xs = new IteratorObservable($generator, $this->scheduler);

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

        $xs = new IteratorObservable($generator, $this->scheduler);

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

        $results = $this->scheduler->startWithCreate(function () use ($generator) {
            return Observable::fromIterator($generator, $this->scheduler);
        });

        $this->assertMessages([
            onNext(201, 1),
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

        $results = $this->scheduler->startWithCreate(function () use ($generator) {
            return Observable::fromIterator($generator, $this->scheduler);
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

        $results = $this->scheduler->startWithDispose(function () use ($generator) {
            return Observable::fromIterator($generator, $this->scheduler);
        }, 202);

        $this->assertMessages([
            onNext(201, 1)
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function it_schedules_all_elements_from_the_generator_with_return()
    {
        $generator = $this->genOneToThreeAndReturn();

        $results = $this->scheduler->startWithCreate(function () use ($generator) {
            return Observable::fromIterator($generator, $this->scheduler);
        });

        $this->assertMessages([
            onNext(201, 1),
            onNext(202, 2),
            onNext(203, 3),
            onNext(204, 10),
            onCompleted(204),
        ], $results->getMessages());
    }

    /**
     * @test
     * RxPHP Issue 188
     */
    public function it_completes_if_subscribed_second_time_without_return_value()
    {
        $generator = $this->genOneToThree();

        $results1 = new MockObserver($this->scheduler);

        $this->scheduler->scheduleAbsolute(200, function () use ($generator, $results1) {
            Observable::fromIterator($generator, $this->scheduler)->subscribe($results1);
        });

        $results2 = new MockObserver($this->scheduler);

        $this->scheduler->scheduleAbsolute(400, function () use ($generator, $results2) {
            Observable::fromIterator($generator, $this->scheduler)->subscribe($results2);
        });

        $this->scheduler->start();

        $this->assertMessages([
            onNext(201, 1),
            onNext(202, 2),
            onNext(203, 3),
            onCompleted(204),
        ], $results1->getMessages());

        $this->assertMessages([
            onCompleted(401),
        ], $results2->getMessages());
    }

    /**
     * @test
     * RxPHP Issue 188
     */
    public function it_returns_value_if_subscribed_second_time_with_return_value()
    {
        $generator = $this->genOneToThreeAndReturn();

        $results1 = new MockObserver($this->scheduler);

        $this->scheduler->scheduleAbsolute(200, function () use ($generator, $results1) {
            Observable::fromIterator($generator, $this->scheduler)->subscribe($results1);
        });

        $results2 = new MockObserver($this->scheduler);

        $this->scheduler->scheduleAbsolute(400, function () use ($generator, $results2) {
            Observable::fromIterator($generator, $this->scheduler)->subscribe($results2);
        });

        $this->scheduler->start();

        $this->assertMessages([
            onNext(201, 1),
            onNext(202, 2),
            onNext(203, 3),
            onNext(204, 10),
            onCompleted(204),
        ], $results1->getMessages());

        $this->assertMessages([
            onNext(401,10),
            onCompleted(401),
        ], $results2->getMessages());
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

    private function genOneToThreeAndReturn()
    {
        for ($i = 1; $i <= 3; $i++) {
            yield $i;
        }

        return 10;
    }
}
