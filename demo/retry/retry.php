<?php
require_once __DIR__ . '/../bootstrap.php';

$loop      = \React\EventLoop\Factory::create();
$scheduler = new \Rx\Scheduler\EventLoopScheduler($loop);

$count = 0;

$observable = Rx\Observable::interval(1000, $scheduler)
    ->flatMap(function ($x) use (&$count) {
        if (++$count < 2) {
            return Rx\Observable::error(new \Exception("Something"));
        }
        return Rx\Observable::just(42);
    })
    ->retry(3)
    ->take(1);

$observable->subscribe($stdoutObserver);

$loop->run();
