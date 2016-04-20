<?php
require_once __DIR__ . '/../bootstrap.php';

$loop = new \React\EventLoop\StreamSelectLoop();

$scheduler = new \Rx\Scheduler\EventLoopScheduler($loop);

$source = \Rx\Observable::timer(200, $scheduler);

$source->subscribe($createStdoutObserver());

$loop->run();
