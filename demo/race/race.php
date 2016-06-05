<?php

require_once __DIR__ . '/../bootstrap.php';

$loop      = \React\EventLoop\Factory::create();
$scheduler = new \Rx\Scheduler\EventLoopScheduler($loop);

$source = Rx\Observable::race(
    [
        Rx\Observable::timer(500)->map(function () {
            return 'foo';
        }),
        Rx\Observable::timer(200)->map(function () {
            return 'bar';
        })
    ]
);

$source->subscribe($stdoutObserver, $scheduler);

$loop->run();
