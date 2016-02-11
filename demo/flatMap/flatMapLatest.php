<?php

require_once __DIR__ . '/../bootstrap.php';

$loop      = \React\EventLoop\Factory::create();
$scheduler = new \Rx\Scheduler\EventLoopScheduler($loop);

$source = \Rx\Observable::range(1, 3)
    ->flatMapLatest(function ($x) {
        return \Rx\Observable::fromArray([$x . 'a', $x . 'b']);
    });

$source->subscribe($stdoutObserver, $scheduler);

$loop->run();