<?php

use Rx\Observable;
use Rx\Scheduler\EventLoopScheduler;
use React\EventLoop\StreamSelectLoop;

$loop = new StreamSelectLoop();
$scheduler = new EventLoopScheduler($loop);

$source = Observable::range(0, 250, $scheduler)
    ->merge(Observable::range(0, 250, $scheduler));

$factory = function() use ($source) {
    return $source;
};

return [$factory, $loop];