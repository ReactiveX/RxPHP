<?php

use Rx\Observable;
use Rx\Scheduler\EventLoopScheduler;
use React\EventLoop\StreamSelectLoop;

$loop = new StreamSelectLoop();
$scheduler = new EventLoopScheduler($loop);

$source = array_map(function($val) {
    return $val % 3;
}, range(0, 25));

return function() use ($source, $dummyObserver, $scheduler, $loop) {
    Observable::fromArray($source)
        ->distinct()
        ->subscribe($dummyObserver, $scheduler);

    $loop->run();
};