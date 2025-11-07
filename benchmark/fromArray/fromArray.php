<?php

use Rx\Observable;
use Rx\Scheduler\ImmediateScheduler;

$scheduler = new ImmediateScheduler();

$source = Observable::fromArray([0, 1, 2, 3, 4, 5, 6, 7, 8, 9], $scheduler);

return [function() use ($source) {
    return $source;
}, $loop];