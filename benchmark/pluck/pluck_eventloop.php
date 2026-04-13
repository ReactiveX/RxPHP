<?php

use Rx\Observable;
use Rx\Scheduler\EventLoopScheduler;
use React\EventLoop\StreamSelectLoop;

$loop = new StreamSelectLoop();
$scheduler = new EventLoopScheduler($loop);

$source = Observable::of([1, 2, 3, 4, 5], $scheduler)
    ->pluck(2);

return function() use ($source) {
    return $source;
};
