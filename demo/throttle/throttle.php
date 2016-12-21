<?php

use React\EventLoop\Factory;
use Rx\Observable;
use Rx\Scheduler\EventLoopScheduler;

require_once __DIR__ . '/../bootstrap.php';

$loop      = Factory::create();
$scheduler = new EventLoopScheduler($loop);

$times = [
    ['value' => 0, 'time' => 100],
    ['value' => 1, 'time' => 600],
    ['value' => 2, 'time' => 300],
    ['value' => 3, 'time' => 900],
    ['value' => 4, 'time' => 200]
];

// Delay each item by time and project value;
$source = Observable::fromArray($times)
    ->flatMap(function ($item) {
        return Observable::just($item['value'])
            ->delay($item['time']);
    })
    ->throttle(300 /* ms */);

$subscription = $source->subscribe($stdoutObserver, $scheduler);

$loop->run();
