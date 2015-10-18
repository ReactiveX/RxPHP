<?php

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Observer\CallbackObserver;

class DoOnNextTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function doOnNext_should_see_all_values()
    {
        $xs = $this->createHotObservable([
          onNext(150, 1),
          onNext(210, 2),
          onNext(220, 3),
          onNext(230, 4),
          onNext(240, 5),
          onCompleted(250)
        ]);

        $i   = 0;
        $sum = 2 + 3 + 4 + 5;

        $this->scheduler->startWithCreate(function () use ($xs, &$i, &$sum) {
            return $xs->doOnNext(function ($x) use (&$i, &$sum) {
                $i++;

                return $sum -= $x;
            });
        });

        $this->assertEquals(4, $i);
        $this->assertEquals(0, $sum);
    }
}
