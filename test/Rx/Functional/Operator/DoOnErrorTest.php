<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use RuntimeException;
use Rx\Functional\FunctionalTestCase;

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

    /**
     * @test
     */
    public function doOnError_should_call_after_resubscription()
    {
        $xs = $this->createColdObservable([
            onError(10, new \Exception("Hello")),
            onCompleted(20)
        ]);

        $messages = [];

        $xs
            ->doOnError(function ($x) use (&$messages) {
                $messages[] = onError($this->scheduler->getClock(), $x);
            })
            ->retry(2)
            ->subscribe(null, function () {}, null, $this->scheduler);

        $this->scheduler->start();

        $this->assertMessages([
            onError(10, new \Exception("Hello")),
            onError(20, new \Exception("Hello"))
        ], $messages);
    }
}
