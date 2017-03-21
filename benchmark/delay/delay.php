<?php

use Rx\Observable;
use Rx\Scheduler\ImmediateScheduler;

$source = Observable::just(25)
    ->delay(0, new ImmediateScheduler());

return function() use ($source) {
    return $source;
};
