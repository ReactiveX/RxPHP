<?php

use Rx\Observable;
use Rx\Scheduler\EventLoopScheduler;
use React\EventLoop\StreamSelectLoop;

$loop = new StreamSelectLoop();
$scheduler = new EventLoopScheduler($loop);

$source = Observable::of(25, $scheduler)
    ->delay(0, $scheduler);

$factory = function() use ($source) {
    return $source;
};

return [$factory, $loop];