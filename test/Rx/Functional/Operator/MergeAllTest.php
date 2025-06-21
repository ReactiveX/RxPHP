<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Exception;
use Rx\Functional\FunctionalTestCase;
use Rx\Observable;

class MergeAllTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function it_passes_on_error_from_sources()
    {
        $xs = $this->createColdObservable([
            onNext(100, 4),
            onNext(200, 2),
            onNext(300, 3),
            onNext(400, 1),
            onCompleted(500)
        ]);

        $ys = $this->createColdObservable([
            onNext(50,  $xs),
            onError(200, new Exception()),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function() use ($ys) {
            return $ys->mergeAll();
        });

        $this->assertMessages([
            onNext(350, 4),
            onError(400, new Exception()),
        ], $results->getMessages());

       $this->assertSubscriptions([subscribe(250, 400)], $xs->getSubscriptions());
       $this->assertSubscriptions([subscribe(200, 400)], $ys->getSubscriptions());
    }

    /**
     * @test
     */
    public function it_passes_on_completed_from_sources()
    {
        $ys = $this->createHotObservable([
            onCompleted(250),
        ]);

        $results = $this->scheduler->startWithCreate(function() use ($ys) {
            return $ys->mergeAll();
        });

        $this->assertMessages([
            onCompleted(250),
        ], $results->getMessages());

       $this->assertSubscriptions([subscribe(200, 250)], $ys->getSubscriptions());
    }
}
