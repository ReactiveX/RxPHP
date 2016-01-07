<?php

namespace Rx\Functional\Observable;

use React\EventLoop\Factory;
use Rx\Functional\FunctionalTestCase;
use Rx\Observer\CallbackObserver;
use Rx\React\Interval;

class IntervalObservableTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function interval_relative_time_basic()
    {
        $loop      = Factory::create();
        $o         = new Interval(100, $loop);
        $goodCount = 0;

        $o->take(7)->subscribe(new CallbackObserver(
            function ($x) use (&$goodCount) {
                $this->assertEquals($x, $goodCount);
                $goodCount++;
            }
        ));

        $loop->run();

        $this->assertEquals(7, $goodCount);

    }
}
