<?php

namespace Rx\Observable;

class ArrayObservableTest extends \PHPUnit_Framework_TestCase
{
    public function testRange()
    {
        $observable = new ArrayObservable(range(1, 10));

        $record = array();
        $observable->subscribeCallback(function($v) use (&$record) { $record[] = $v; });

        $this->assertEquals(range(1, 10), $record);
    }

    public function testOnCompleteIsCalled()
    {
        $observable = new ArrayObservable(array());

        $isCalled = false;
        $observable->subscribeCallback(null, null, function() use (&$isCalled) { $isCalled = true; });

        $this->assertTrue($isCalled, "onComplete should be called.");
    }
}
