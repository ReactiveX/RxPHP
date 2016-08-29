<?php

require_once __DIR__ . '/../bootstrap.php';

$loop      = \React\EventLoop\Factory::create();
$scheduler = new \Rx\Scheduler\EventLoopScheduler($loop);

$source = Rx\Observable::fromArray([
    \Rx\Observable::interval(100)->mapTo('a'),
    \Rx\Observable::interval(200)->mapTo('b'),
    \Rx\Observable::interval(300)->mapTo('c'),
])
    ->switchFirst()
    ->take(3);

$subscription = $source->subscribe($stdoutObserver, $scheduler);

$loop->run();
