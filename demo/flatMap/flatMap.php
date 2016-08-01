<?php

require_once __DIR__ . '/../bootstrap.php';

$loop      = React\EventLoop\Factory::create();
$scheduler = new Rx\Scheduler\EventLoopScheduler($loop);

$observable = Rx\Observable::range(1, 5);

$selectManyObservable = $observable->flatMap(function ($value) {
    return Rx\Observable::range(1, $value);
});

$disposable = $selectManyObservable->subscribe($stdoutObserver, $scheduler);

$loop->run();
