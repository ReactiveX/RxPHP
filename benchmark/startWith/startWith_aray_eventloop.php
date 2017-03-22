<?php

use Rx\Observable;
use Rx\Scheduler\EventLoopScheduler;
use React\EventLoop\StreamSelectLoop;

$loop = new StreamSelectLoop();
$scheduler = new EventLoopScheduler($loop);

$source = Observable::range(0, 25, $scheduler)
    ->startWithArray([5, 5, 5]);

return function() use ($source) {
    return $source;
};