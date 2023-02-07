<?php

use Rx\Observable;
use Rx\Scheduler\ImmediateScheduler;

$scheduler = new ImmediateScheduler();

$source = Observable::range(0, 25, $scheduler)
    ->scan(function($acc, $x) {
        return $acc + $x;
    });

return function() use ($source) {
    return $source;
};
