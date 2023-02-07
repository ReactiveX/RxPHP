<?php

use Rx\Observable;
use Rx\Scheduler\ImmediateScheduler;

$scheduler = new ImmediateScheduler();

$source = Observable::range(1, 25, $scheduler)
    ->concatMap(function($x) use ($scheduler) {
        return Observable::range($x, 25, $scheduler);
    });

return function() use ($source) {
    return $source;
};
