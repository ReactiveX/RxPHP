<?php

use Rx\Observable;
use Rx\Scheduler\EventLoopScheduler;
use React\EventLoop\StreamSelectLoop;

$loop = new StreamSelectLoop();
$scheduler = new EventLoopScheduler($loop);

$source = Observable::range(0, 25, $scheduler)
    ->map(function($i) {
        return ['key' => $i % 5];
    })
    ->groupBy(function($item) {
        return $item['key'];
    });

$factory = function() use ($source) {
    return $source;
};

return [$factory, $loop];