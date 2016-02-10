<?php

require_once __DIR__ . '/../bootstrap.php';

$loop      = \React\EventLoop\Factory::create();
$scheduler = new \Rx\Scheduler\EventLoopScheduler($loop);

$obs = \Rx\Observable::interval(100, $scheduler)
    ->take(3)
    ->mapWithIndex(function ($i) {
        return $i;
    });

$source = Rx\Observable::range(0, 5)
    ->concatMapTo($obs);

$subscription = $source->subscribe($stdoutObserver);

$loop->run();
