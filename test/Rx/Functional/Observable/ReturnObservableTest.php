<?php

namespace Rx\Functional\Observable;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable\ReturnObservable;
use Rx\Observer\CallbackObserver;

class ReturnObservableTest extends FunctionalTestCase
{
    public function testReturnObservableSubscribeTwice()
    {
        $o = new ReturnObservable("The Value");

        $goodCount = 0;

        $o->subscribe(new CallbackObserver(
            function ($x) use (&$goodCount) {
                $this->assertEquals("The Value", $x);
                $goodCount++;
            }
        ));

        $o->subscribe(new CallbackObserver(
            function ($x) use (&$goodCount) {
                $this->assertEquals("The Value", $x);
                $goodCount++;
            }
        ));

        $this->assertEquals(2, $goodCount);
    }
}