<?php

use Rx\Observable;
use Rx\Scheduler\EventLoopScheduler;
use React\EventLoop\StreamSelectLoop;

$loop = new StreamSelectLoop();
$scheduler = new EventLoopScheduler($loop);

return function() use ($dummyObserver, $scheduler, $loop) {
    Observable::range(0, 25)
        ->bufferWithCount(5)
        ->subscribe($dummyObserver, $scheduler);

    $loop->run();
};