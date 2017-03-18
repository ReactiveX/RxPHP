<?php

use Rx\Observable;
use Rx\Scheduler\EventLoopScheduler;
use React\EventLoop\StreamSelectLoop;

$loop = new StreamSelectLoop();
$scheduler = new EventLoopScheduler($loop);

$source = Observable::range(0, 50, $scheduler)
    ->filter(function($value) {
        return $value % 2 == 0;
    })
    ->filter(function($value) {
        return $value % 10 == 0;
    });

$factory = function() use ($source) {
    return $source;
};

return [$factory, $loop];