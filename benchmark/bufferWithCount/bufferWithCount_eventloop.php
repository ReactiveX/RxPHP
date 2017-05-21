<?php

use Rx\Observable;
use Rx\Scheduler\EventLoopScheduler;
use React\EventLoop\StreamSelectLoop;

$loop = new StreamSelectLoop();
$scheduler = new EventLoopScheduler($loop);

$source = Observable::just(25)
    ->delay(0, $scheduler);

$factory = function() use ($source, $scheduler) {
    return $source;
};

return [$factory, $loop];