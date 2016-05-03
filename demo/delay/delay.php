<?php
require_once __DIR__ . '/../bootstrap.php';

$loop = new \React\EventLoop\StreamSelectLoop();

$scheduler  = new \Rx\Scheduler\EventLoopScheduler($loop);

\Rx\Observable::interval(1000, $scheduler)
    ->doOnNext(function ($x) {
        echo "Side effect: " . $x . "\n";
    })
    ->delay(500)
    ->take(5)
    ->subscribe($createStdoutObserver(), $scheduler);

$loop->run();
