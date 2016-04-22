<?php

require_once __DIR__ . '/../bootstrap.php';

$loop      = React\EventLoop\Factory::create();
$scheduler = new Rx\Scheduler\EventLoopScheduler($loop);

$observable       = Rx\Observable::just(42)->repeat();
$otherObservable  = Rx\Observable::just(21)->repeat();
$mergedObservable = $observable
    ->merge($otherObservable)
    ->take(10);

$disposable = $mergedObservable->subscribe($stdoutObserver, $scheduler);

$loop->run();
