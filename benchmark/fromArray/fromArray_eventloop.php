<?php

use Rx\Observable;
use React\EventLoop\StreamSelectLoop;
use Rx\Scheduler;

$loop = new StreamSelectLoop();
$scheduler = new Scheduler\EventLoopScheduler($loop);

$source = Observable::fromArray([0, 1, 2, 3, 4, 5, 6, 7, 8, 9], $scheduler);

$factory = function() use ($source) {
    return $source;
};

return [$factory, $loop];