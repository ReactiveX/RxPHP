<?php

namespace Rx\Functional\Operator;

use RuntimeException;
use Rx\Functional\FunctionalTestCase;
use Rx\Observer\CallbackObserver;

class DoOnCompletedTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function doOnCompleted_should_be_called()
    {
        $xs = $this->createHotObservable([
          onNext(150, 1),
          onNext(210, 2),
          onNext(220, 3),
          onCompleted(250)
        ]);

        $called = 0;

        $this->scheduler->startWithCreate(function () use ($xs, &$called) {
            return $xs->doOnCompleted(function () use (&$called) {
                $called++;
            });
        });

        $this->assertEquals(1, $called);
    }
}
