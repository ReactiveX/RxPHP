<?php

use Rx\Observable;
use Rx\Scheduler\ImmediateScheduler;

$scheduler = new ImmediateScheduler();

$source = Observable::defer(function() use ($scheduler) {
    return Observable::forkJoin([
        Observable::of(25, $scheduler),
        Observable::range(0, 25, $scheduler),
        Observable::fromArray([1, 2, 3, 4, 5], $scheduler)
    ]);
}, $scheduler);

return function() use ($source) {
    return $source;
};
