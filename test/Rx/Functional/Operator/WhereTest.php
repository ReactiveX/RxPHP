<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Exception;
use Rx\Functional\FunctionalTestCase;
use Rx\Observable\ReturnObservable;

class WhereTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function it_filters_all_on_false(): void
    {
        $xs = $this->createHotObservableWithData();

        $results = $this->scheduler->startWithCreate(function() use ($xs) {
            return $xs->where(function($elem) { return false; });
        });


        $this->assertMessages([
            onCompleted(820),
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function it_passes_all_on_true(): void 
    {
        $xs = $this->createHotObservableWithData();

        $results = $this->scheduler->startWithCreate(function() use ($xs) {
            return $xs->where(function($elem) { return true; });
        });

        $this->assertMessages([
            onNext(300, 21),
            onNext(500, 42),
            onNext(800, 84),
            onCompleted(820),
        ], $results->getMessages());
    }


    /**
     * @test
     */
    public function it_passes_on_error(): void
    {
        $exception = new Exception();
        $xs = $this->createHotObservable([
            onNext(500, 42),
            onError(820, $exception),
        ]);

        $results = $this->scheduler->startWithCreate(function() use ($xs) {
            return $xs->where(function($elem) { return $elem === 42; });
        });

        $this->assertMessages([
            onNext(500, 42),
            onError(820, $exception),
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function calls_on_error_if_predicate_throws_an_exception(): void
    {
        $xs = $this->createHotObservable([
            onNext(500, 42),
        ]);

        $results = $this->scheduler->startWithCreate(function() use ($xs) {
            return $xs->where(function(): void { throw new Exception(); });
        });

        $this->assertMessages([onError(500, new Exception())], $results->getMessages());
    }

    protected function createHotObservableWithData()
    {
        return $this->createHotObservable([
            onNext(100,  2),
            onNext(300, 21),
            onNext(500, 42),
            onNext(800, 84),
            onCompleted(820),
        ]);
    }
}
