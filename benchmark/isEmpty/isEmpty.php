<?php

use Rx\Observable;
use Rx\Scheduler\ImmediateScheduler;

$scheduler = new ImmediateScheduler();

$source = Observable::of(25, $scheduler)
    ->isEmpty();

return function() use ($source) {
    return $source;
};