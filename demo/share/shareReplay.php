<?php

require_once __DIR__ . '/../bootstrap.php';

$loop      = \React\EventLoop\Factory::create();
$scheduler = new \Rx\Scheduler\EventLoopScheduler($loop);

$interval = Rx\Observable::interval(1000);

$source = $interval
    ->take(4)
    ->doOnNext(function ($x) {
        echo 'Side effect', PHP_EOL;
    });

$published = $source
    ->shareReplay(3);

$published->subscribe($createStdoutObserver('SourceA '), $scheduler);
$published->subscribe($createStdoutObserver('SourceB '), $scheduler);

Rx\Observable
    ::just(true)
    ->concatMapTo(\Rx\Observable::timer(6000))
    ->flatMap(function () use ($published) {
        return $published;
    })
    ->subscribe($createStdoutObserver('SourceC '), $scheduler);

$loop->run();

