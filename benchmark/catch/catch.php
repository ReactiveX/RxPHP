<?php

use Rx\Observable;
use Rx\Scheduler\ImmediateScheduler;

$scheduler = new ImmediateScheduler();

$source = Observable::error(new \Exception('error'), $scheduler)
    ->catch(function() use ($scheduler) {
        return Observable::of(25, $scheduler);
    });

return function() use ($source) {
    return $source;
};
