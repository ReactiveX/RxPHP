<?php

use Rx\Observable;
use Rx\Scheduler\ImmediateScheduler;

$scheduler = new ImmediateScheduler();

$source = Observable::range(0, 50, $scheduler)
    ->skipWhile(function($value) {
        return $value < 25;
    });

return function() use ($source) {
    return $source;
};
