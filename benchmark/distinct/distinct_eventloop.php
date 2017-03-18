<?php

use Rx\Observable;
use Rx\Observable\ArrayObservable;
use Rx\Scheduler\EventLoopScheduler;
use React\EventLoop\StreamSelectLoop;

$loop = new StreamSelectLoop();
$scheduler = new EventLoopScheduler($loop);

$range = array_map(function($val) {
    return $val % 3;
}, range(0, 25));

$source = (new ArrayObservable($range, $scheduler))
    ->distinct();

$factory = function() use ($source) {
    return $source;
};

return [$factory, $loop];