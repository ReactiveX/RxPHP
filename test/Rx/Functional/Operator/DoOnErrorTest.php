<?php

namespace Rx\Functional\Operator;

use RuntimeException;
use Rx\Functional\FunctionalTestCase;
use Rx\Observer\CallbackObserver;

class DoOnErrorTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function doOnError_should_see_errors()
    {
        $ex = new RuntimeException('boom!');
        $xs = $this->createHotObservable([
          onNext(150, 1),
          onNext(210, 2),
          onError(220, $ex),
          onCompleted(250)
        ]);

        $called = 0;
        $error = null;

        $this->scheduler->startWithCreate(function () use ($xs, &$called, &$error) {
            return $xs->doOnError(function ($err) use (&$called, &$error) {
                $called++;
                $error = $err;
            });
        });

        $this->assertEquals(1, $called);
        $this->assertSame($ex, $error);
    }
}
