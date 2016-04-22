<?php

require_once __DIR__ . '/../bootstrap.php';

$loop      = new \React\EventLoop\StreamSelectLoop();
$scheduler = new \Rx\Scheduler\EventLoopScheduler($loop);

$source = Rx\Observable::interval(1000)
    ->map(function ($n) {
        if ($n === 2) {
            throw new Exception();
        }
        return $n;
    })
    ->retryWhen(function ($errors) {
        return $errors->delay(200);
    })
    ->take(6);

$subscription = $source->subscribe($createStdoutObserver(), $scheduler);

$loop->run();
