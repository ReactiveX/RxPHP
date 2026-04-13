<?php

use Rx\Observable;
use Rx\Scheduler\ImmediateScheduler;

$scheduler = new ImmediateScheduler();

$source = Observable::of([1, 2, 3, 4, 5], $scheduler)
    ->pluck(2);

return function() use ($source) {
    return $source;
};
