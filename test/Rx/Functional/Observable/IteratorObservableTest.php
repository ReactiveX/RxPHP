<?php

namespace Rx\Functional\Observable;


use Rx\Functional\FunctionalTestCase;
use Rx\Observable;

class IteratorObservableTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function it_schedules_all_elements_from_the_generator()
    {
        $generator = $this->genOneToThree();

        $xs = new \Rx\Observable\IteratorObservable($generator);

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

        $xs = new \Rx\Observable\IteratorObservable($generator);

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

        $xs = new \Rx\Observable\IteratorObservable($generator);

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
    public function generator_throws_error()
    {
        $error     = new \Exception();
        $generator = $this->genError($error);

        $xs = new \Rx\Observable\IteratorObservable($generator);

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

        $xs = new \Rx\Observable\IteratorObservable($generator);

        $results = $this->scheduler->startWithDispose(function () use ($xs) {
            return $xs;
        }, 202);

        $this->assertMessages([
            onNext(201, 1)
        ], $results->getMessages());
    }

    /**
     * @test
     *
     * @link https://github.com/ReactiveX/RxPHP/issues/39
     */
    public function splObjectStorage_many()
    {

        if (defined('HHVM_VERSION') && version_compare(HHVM_VERSION, '3.11.0', 'lt')) {
            $this->markTestSkipped();
        }
        
        $spl = new \SplObjectStorage();

        $a = (object)["prop" => 1];
        $b = (object)["prop" => 2];
        $c = (object)["prop" => 3];

        $spl->attach($a);
        $spl->attach($b);
        $spl->attach($c);

        $spl->rewind();

        $xs = new \Rx\Observable\IteratorObservable($spl);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs;
        });

        $this->assertMessages([
            onNext(201, $a),
            onNext(202, $b),
            onNext(203, $c),
            onCompleted(204)
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
