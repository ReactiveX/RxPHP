<?php

use Rx\Observable;
use Rx\Scheduler\EventLoopScheduler;
use React\EventLoop\StreamSelectLoop;

$loop = new StreamSelectLoop();
$scheduler = new EventLoopScheduler($loop);

$range = Observable::range(0, 25, $scheduler);
$source = $range
    ->skipUntil($range->take(3));

$factory = function() use ($source) {
    return $source;
};

return [$factory, $loop];