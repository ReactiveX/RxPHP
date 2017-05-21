<?php

use Rx\Observable;
use Rx\Scheduler\EventLoopScheduler;
use React\EventLoop\StreamSelectLoop;

$loop = new StreamSelectLoop();
$scheduler = new EventLoopScheduler($loop);

$source = Observable::range(0, 50, $scheduler)
    ->skip(25);

$factory = function() use ($source) {
    return $source;
};

return [$factory, $loop];