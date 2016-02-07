<?php

require_once __DIR__ . '/../bootstrap.php';

$loop      = React\EventLoop\Factory::create();
$scheduler = new Rx\Scheduler\EventLoopScheduler($loop);

$observable       = Rx\Observable::just(42)->repeat();
$otherObservable  = Rx\Observable::just(21)->repeat();
$mergedObservable = $observable->merge($otherObservable, $scheduler);

$disposable = $mergedObservable->subscribe($stdoutObserver, $scheduler);

$loop->addPeriodicTimer(0.01, function () {
    $memory    = memory_get_usage() / 1024;
    $formatted = number_format($memory, 3) . 'K';
    echo "Current memory usage: {$formatted}\n";
});

$loop->run();


//Next value: 42
//Next value: 21
//Next value: 42
//Next value: 21
//Next value: 42
//Next value: 21
//Next value: 42
//Next value: 21
//Current memory usage: 838.547K
