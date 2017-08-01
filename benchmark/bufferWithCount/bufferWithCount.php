<?php

use Rx\Observable;
use Rx\Scheduler\ImmediateScheduler;

$source = Observable::range(0, 25, new ImmediateScheduler())
    ->bufferWithCount(5);

return function() use ($source) {
    return $source;
};
