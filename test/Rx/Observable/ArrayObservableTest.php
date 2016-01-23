<?php

namespace Rx\Observable;

use Rx\Observer\CallbackObserver;

class ArrayObservableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_starts_again_if_you_subscribe_again()
    {
        $a = [1,2,3];

        $o = new ArrayObservable($a);

        $goodCount = 0;

        $o->toArray()->subscribe(new CallbackObserver(
            function ($x) use ($a, &$goodCount) {
                $goodCount++;
                $this->assertEquals($a, $x);
            }
        ));

        $o->toArray()->subscribe(new CallbackObserver(
            function ($x) use ($a, &$goodCount) {
                $goodCount++;
                $this->assertEquals($a, $x);
            }
        ));

        $this->assertEquals(2, $goodCount);
    }

    public function testRange()
    {
        //todo: refactor
        $observable = new ArrayObservable(range(1, 10));

        $record = array();
        $observable->subscribeCallback(function($v) use (&$record) { $record[] = $v; });

        $this->assertEquals(range(1, 10), $record);
    }

    public function testOnCompleteIsCalled()
    {
        //todo: refactor
        $observable = new ArrayObservable(array());

        $isCalled = false;
        $observable->subscribeCallback(null, null, function() use (&$isCalled) { $isCalled = true; });

        $this->assertTrue($isCalled, "onComplete should be called.");
    }
}
