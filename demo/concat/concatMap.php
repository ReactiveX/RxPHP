<?php

require_once __DIR__ . '/../bootstrap.php';

$loop      = \React\EventLoop\Factory::create();
$scheduler = new \Rx\Scheduler\EventLoopScheduler($loop);

$source = Rx\Observable::range(0, 5)
    ->concatMap(function ($x, $i) use ($scheduler) {
        return \Rx\Observable::interval(100, $scheduler)
            ->take($x)
            ->map(function () use ($i) {
                return $i;
            });
    });

$subscription = $source->subscribe($stdoutObserver);

$loop->run();
