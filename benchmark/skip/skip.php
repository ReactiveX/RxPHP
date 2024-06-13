<?php

use Rx\Observable;
use Rx\Scheduler\ImmediateScheduler;

$scheduler = new ImmediateScheduler();

$source = Observable::range(0, 50, $scheduler)
    ->skip(25);

return function() use ($source) {
    return $source;
};