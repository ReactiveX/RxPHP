<?php

use Rx\Observable;
use Rx\Observable\ArrayObservable;
use Rx\Scheduler\EventLoopScheduler;
use React\EventLoop\StreamSelectLoop;

$loop = new StreamSelectLoop();
$scheduler = new EventLoopScheduler($loop);

$source = Observable::range(0, 25, $scheduler)
    ->map(function($i) {
        return $i % 3;
    })
    ->distinct();

$factory = function() use ($source) {
    return $source;
};

return [$factory, $loop];