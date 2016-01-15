<?php

require_once __DIR__ . '/../bootstrap.php';

$loop = new \React\EventLoop\StreamSelectLoop();

$scheduler  = new \Rx\Scheduler\EventLoopScheduler($loop);

Rx\Observable::interval(1000)
    ->timeout(500)
    ->subscribe($createStdoutObserver("One second - "), $scheduler);

Rx\Observable::interval(100)
    ->take(3)
    ->timeout(500)
    ->subscribe($createStdoutObserver("100 ms     - "), $scheduler);

$loop->run();

// Output:
//100 ms     - Next value: 0
//100 ms     - Next value: 1
//100 ms     - Next value: 2
//100 ms     - Complete!
//One second - Exception: timeout
