<?php

require_once __DIR__ . '/../bootstrap.php';

$loop      = React\EventLoop\Factory::create();
$scheduler = new Rx\Scheduler\EventLoopScheduler($loop);

$observable = Rx\Observable::range(1, 5);

$selectManyObservable = $observable->flatMapTo(\Rx\Observable::range(0,2));

$disposable = $selectManyObservable->subscribe($stdoutObserver, $scheduler);

$loop->run();
