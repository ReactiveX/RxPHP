<?php

use Rx\Observable;
use Rx\Scheduler\ImmediateScheduler;

$scheduler = new ImmediateScheduler();

$source = Observable::range(0, 25, $scheduler)
    ->combineLatest([Observable::range(0, 25, $scheduler)], function($a, $b) {
        return $a + $b;
    });

return function() use ($source) {
    return $source;
};
