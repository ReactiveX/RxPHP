<?php

use Rx\Observable;
use Rx\Scheduler\ImmediateScheduler;

$scheduler = new ImmediateScheduler();

$source = Observable::range(0, 25, $scheduler)
    ->map(function() use ($scheduler) {
        return Observable::range(0, 25, $scheduler);
    })
    ->concatAll();

return function() use ($source) {
    return $source;
};
