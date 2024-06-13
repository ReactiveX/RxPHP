<?php

use Rx\Observable;
use Rx\Scheduler\ImmediateScheduler;

$scheduler = new ImmediateScheduler();

$range = Observable::range(0, 25, $scheduler);
$source = $range
    ->skipUntil($range->take(3));

$factory = function() use ($source) {
    return $source;
};

return [$factory, $loop];