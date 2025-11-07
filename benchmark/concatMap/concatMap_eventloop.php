<?php

use Rx\Observable;
use Rx\Scheduler\EventLoopScheduler;
use React\EventLoop\StreamSelectLoop;

$loop = new StreamSelectLoop();
$scheduler = new EventLoopScheduler($loop);

$source = Observable::range(1, 25, $scheduler)
    ->concatMap(function($x) use ($scheduler) {
        return Observable::range($x, 25, $scheduler);
    });

$factory = function() use ($source) {
    return $source;
};

return [$factory, $loop];