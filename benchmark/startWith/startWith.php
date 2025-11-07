<?php

use Rx\Observable;
use Rx\Scheduler\ImmediateScheduler;

$scheduler = new ImmediateScheduler();

$source = Observable::range(0, 25, $scheduler)
    ->startWith(5, $scheduler);

return function() use ($source) {
    return $source;
};