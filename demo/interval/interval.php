<?php
require_once __DIR__ . '/../bootstrap.php';

$loop = new \React\EventLoop\StreamSelectLoop();

$scheduler  = new \Rx\Scheduler\EventLoopScheduler($loop);

$disposable = \Rx\Observable::interval(1000, $scheduler)
    ->take(5)
    ->subscribe($createStdoutObserver());

$loop->run();
