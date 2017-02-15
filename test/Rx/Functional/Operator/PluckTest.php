<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;

class PluckTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function pluck_completed()
    {
        $xs = $this->createHotObservable([
            onNext(180, (object)['prop' => 1]),
            onNext(210, (object)['prop' => 2]),
            onNext(240, (object)['prop' => 3]),
            onNext(290, (object)['prop' => 4]),
            onNext(350, (object)['prop' => 5]),
            onCompleted(400),
            onNext(410, (object)['prop' => -1]),
            onCompleted(420),
            onError(430, new \Exception())
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->pluck('prop');
        });

        $this->assertMessages([
            onNext(210, 2),
            onNext(240, 3),
            onNext(290, 4),
            onNext(350, 5),
            onCompleted(400)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 400)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function pluck_error()
    {
        $xs = $this->createHotObservable([
            onNext(180, ['prop' => 1]),
            onNext(210, ['prop' => 2]),
            onNext(240, ['prop' => 3]),
            onNext(290, ['prop' => 4]),
            onNext(350, ['prop' => 5]),
            onError(400, new \Exception())
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->pluck('prop');
        });

        $this->assertMessages([
            onNext(210, 2),
            onNext(240, 3),
            onNext(290, 4),
            onNext(350, 5),
            onError(400, new \Exception())
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 400)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function pluck_completed_array()
    {
        $xs = $this->createHotObservable([
            onNext(180, ['prop' => 1]),
            onNext(210, ['prop' => 2]),
            onNext(240, ['prop' => 3]),
            onNext(290, ['prop' => 4]),
            onNext(350, ['prop' => 5]),
            onCompleted(400)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->pluck('prop');
        });

        $this->assertMessages([
            onNext(210, 2),
            onNext(240, 3),
            onNext(290, 4),
            onNext(350, 5),
            onCompleted(400)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 400)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function pluck_array_index_missing()
    {
        $xs = $this->createHotObservable([
            onNext(180, ['prop' => 1]),
            onNext(210, ['prop' => 2]),
            onNext(240, ['prop' => 3]),
            onNext(290, []),
            onNext(350, ['prop' => 5]),
            onError(400, new \Exception())
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->pluck('prop');
        });

        $this->assertMessages([
            onNext(210, 2),
            onNext(240, 3),
            onError(290, new \Exception())
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 290)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function pluck_object_property_missing()
    {
        $xs = $this->createHotObservable([
            onNext(180, (object)['prop' => 1]),
            onNext(210, (object)['prop' => 2]),
            onNext(240, (object)['prop' => 3]),
            onNext(290, new \stdClass()),
            onNext(350, (object)['prop' => 5]),
            onError(400, new \Exception())
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->pluck('prop');
        });

        $this->assertMessages([
            onNext(210, 2),
            onNext(240, 3),
            onError(290, new \Exception())
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 290)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function pluck_array_numeric_index()
    {
        $xs = $this->createHotObservable([
            onNext(180, [-1,-1,-1,-1]),
            onNext(210, [4,3,2,1]),
            onNext(240, [4,3,20,10]),
            onNext(290, [4,3,200,100]),
            onNext(350, [4,3,2000,1000]),
            onCompleted(400)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->pluck(2);
        });

        $this->assertMessages([
            onNext(210, 2),
            onNext(240, 20),
            onNext(290, 200),
            onNext(350, 2000),
            onCompleted(400)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 400)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function pluck_nested()
    {
        $xs = $this->createHotObservable([
            onNext(180, [-1,-1,-1,-1]),
            onNext(210, [[1],[2],[3]]),
            onNext(240, [[4],[5],[6]]),
            onNext(290, [[7],[8],[9]]),
            onNext(350, [[10],[11],[12]]),
            onCompleted(400)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->pluck(1, 0);
        });

        $this->assertMessages([
            onNext(210, 2),
            onNext(240, 5),
            onNext(290, 8),
            onNext(350, 11),
            onCompleted(400)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 400)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function pluck_nested_numeric_key_with_object_properties()
    {
        $xs = $this->createHotObservable([
            onNext(180, [-1,-1,-1,-1]),
            onNext(210, [[1],(object)['prop' => 2],[3]]),
            onNext(240, [[4],(object)['prop' => 5],[6]]),
            onNext(290, [[7],(object)['prop' => 8],[9]]),
            onNext(350, [[10],(object)['prop' => 11],[12]]),
            onCompleted(400)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->pluck(1, 'prop');
        });

        $this->assertMessages([
            onNext(210, 2),
            onNext(240, 5),
            onNext(290, 8),
            onNext(350, 11),
            onCompleted(400)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 400)
        ], $xs->getSubscriptions());
    }
}
