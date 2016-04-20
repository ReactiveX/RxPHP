<?php

require_once __DIR__ . '/../bootstrap.php';

$loop = new \React\EventLoop\StreamSelectLoop();

$scheduler = new \Rx\Scheduler\EventLoopScheduler($loop);

$source = \Rx\Observable::interval(1000, $scheduler)
    ->timestamp()
    ->map(function (\Rx\Timestamped $x) {
        return $x->getValue() . ':' . $x->getTimestampMillis();
    })
    ->take(5);

$source->subscribe($createStdoutObserver());

$loop->run();

// Next value: 0:1460781738354
// Next value: 1:1460781739358
// Next value: 2:1460781740359
// Next value: 3:1460781741362
// Next value: 4:1460781742367
// Complete!
