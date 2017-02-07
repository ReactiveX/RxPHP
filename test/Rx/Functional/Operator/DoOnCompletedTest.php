<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;

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

    /**
     * @test
     */
    public function doOnCompleted_should_call_after_resubscription()
    {
        $xs = $this->createColdObservable([
            onNext(10, 1),
            onCompleted(20)
        ]);

        $messages = [];

        $xs
            ->doOnCompleted(function () use (&$messages) {
                $messages[] = onCompleted($this->scheduler->getClock());
            })
            ->repeat(2)
            ->subscribe(null, null, null, $this->scheduler);

        $this->scheduler->start();

        $this->assertMessages([
            onCompleted(20),
            onCompleted(40)
        ], $messages);
    }
}
