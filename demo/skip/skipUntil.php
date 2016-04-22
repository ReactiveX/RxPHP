<?php

require_once __DIR__ . '/../bootstrap.php';

$loop      = \React\EventLoop\Factory::create();
$scheduler = new \Rx\Scheduler\EventLoopScheduler($loop);

$observable = Rx\Observable::interval(1000)
    ->skipUntil(\Rx\Observable::timer(5000))
    ->take(3);

$observable->subscribe($stdoutObserver, $scheduler);

$loop->run();
