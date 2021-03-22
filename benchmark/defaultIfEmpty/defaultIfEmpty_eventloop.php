<?php

use Rx\Observable;
use Rx\Scheduler\EventLoopScheduler;
use React\EventLoop\StreamSelectLoop;

$loop = new StreamSelectLoop();
$scheduler = new EventLoopScheduler($loop);

$source = Observable::empty($scheduler)
    ->defaultIfEmpty(Observable::of(25, $scheduler));

return function() use ($source) {
    return $source;
};
