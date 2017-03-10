<?php

declare(strict_types = 1);

namespace Rx\Functional\Observable;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable\ReturnObservable;
use Rx\Observer\CallbackObserver;
use Rx\Scheduler;

class ReturnObservableTest extends FunctionalTestCase
{
    public function testReturnObservableSubscribeTwice()
    {
        $o = new ReturnObservable('The Value', Scheduler::getImmediate());

        $goodCount = 0;

        $o->subscribe(new CallbackObserver(
            function ($x) use (&$goodCount) {
                $this->assertEquals('The Value', $x);
                $goodCount++;
            }
        ));

        $o->subscribe(new CallbackObserver(
            function ($x) use (&$goodCount) {
                $this->assertEquals('The Value', $x);
                $goodCount++;
            }
        ));

        $this->assertEquals(2, $goodCount);
    }
}