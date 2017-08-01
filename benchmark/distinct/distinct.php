<?php

use Rx\Observable;
use Rx\Scheduler\ImmediateScheduler;

$scheduler = new ImmediateScheduler();

$source = Observable::range(0, 25, $scheduler)
    ->map(function($i) {
        return $i % 3;
    })
    ->distinct();

return function() use ($source) {
    return $source;
};
