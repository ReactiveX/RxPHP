<?php

use Rx\Observable;
use Rx\Scheduler\ImmediateScheduler;

$scheduler = new ImmediateScheduler();

$source = Observable::range(0, 50, $scheduler)
    ->filter(function($value) {
        return $value % 2 == 0;
    })
    ->filter(function($value) {
        return $value % 10 == 0;
    });

return function() use ($source) {
    return $source;
};
