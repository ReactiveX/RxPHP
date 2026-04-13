<?php

use Rx\Observable;
use Rx\Scheduler\ImmediateScheduler;

$scheduler = new ImmediateScheduler();

$source = Observable::range(0, 5, $scheduler)
    ->timestamp($scheduler);

return function() use ($source) {
    return $source;
};
