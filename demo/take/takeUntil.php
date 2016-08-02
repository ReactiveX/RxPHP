<?php

require_once __DIR__ . '/../bootstrap.php';

$loop = \React\EventLoop\Factory::create();

$scheduler = new \Rx\Scheduler\EventLoopScheduler($loop);

$source = \Rx\Observable::interval(105, $scheduler)
    ->takeUntil(\Rx\Observable::timer(1000));

$subscription = $source->subscribe($stdoutObserver, $scheduler);

$loop->run();
