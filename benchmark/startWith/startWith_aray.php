<?php

use Rx\Observable;
use Rx\Scheduler\ImmediateScheduler;

$scheduler = new ImmediateScheduler();

$source = Observable::range(0, 25, $scheduler)
    ->startWithArray([5, 5, 5], $scheduler);

return function() use ($source) {
    return $source;
};