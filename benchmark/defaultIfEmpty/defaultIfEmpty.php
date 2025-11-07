<?php

use Rx\Observable;
use Rx\Scheduler\ImmediateScheduler;

$scheduler = new ImmediateScheduler();

$source = Observable::empty($scheduler)
    ->defaultIfEmpty(Observable::of(25, $scheduler));

return function() use ($source) {
    return $source;
};
