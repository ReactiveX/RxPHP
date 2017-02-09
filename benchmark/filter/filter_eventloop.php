<?php

use Rx\Observable;
use Rx\Scheduler\EventLoopScheduler;
use React\EventLoop\StreamSelectLoop;

$loop = new StreamSelectLoop();
$scheduler = new EventLoopScheduler($loop);

return function() use ($dummyObserver, $scheduler, $loop) {
    Observable::range(0, 50)
        ->filter(function($value) {
            return $value % 2 == 0;
        })
        ->filter(function($value) {
            return $value % 10 == 0;
        })
        ->subscribe($dummyObserver, $scheduler);

    $loop->run();
};