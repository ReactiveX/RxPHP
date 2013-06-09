<?php

require_once __DIR__ . '/../bootstrap.php';

$loop = React\EventLoop\Factory::create();
$scheduler = new Rx\Scheduler\EventLoopScheduler($loop);

$observable = new Rx\Observable\ArrayObservable(range(1, 5), $scheduler);

$selectManyObservable = $observable->selectMany(function($value) {
    return new Rx\Observable\ArrayObservable(range(1, $value));
}, $scheduler);

$disposable = $selectManyObservable->subscribe($stdoutObserver, $scheduler);

$loop->run();
