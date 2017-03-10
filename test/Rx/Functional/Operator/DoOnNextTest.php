<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;

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
    
    /**
     * @test
     */
    public function doOnNext_should_call_after_resubscription()
    {
        $xs = $this->createColdObservable([
            onNext(10, 1),
            onCompleted(20)
        ]);
        
        $messages = [];
        
        $xs
            ->doOnNext(function ($x) use (&$messages) {
                $messages[] = onNext($this->scheduler->getClock(), $x);
            })
            ->repeat(2)
            ->subscribe(null, null, null, $this->scheduler);
        
        $this->scheduler->start();
        
        $this->assertMessages([
            onNext(10, 1),
            onNext(30, 1)
        ], $messages);
    }
}
